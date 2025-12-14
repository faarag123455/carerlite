<?php
declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Job;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\AuthAccessTokenBlacklistRepository;
use App\Security\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class JobController extends AbstractController
{
    /**
     * Helper: fetch the authenticated User from the JWT (cookie or Bearer).
     */
    private function resolveUserFromJwt(
        Request $request,
        JwtService $jwt,
        UserRepository $users,
        AuthAccessTokenBlacklistRepository $blacklistRepo
    ): ?User {
        $token = null;

        // 1) Authorization: Bearer ...
        $authHeader = $request->headers->get('Authorization', '');
        if (\preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
            $token = \trim($m[1]);
        } else {
            // 2) Fallback to cookie set by /login
            $token = (string) $request->cookies->get('access_token', '');
        }

        if ($token === '') {
            return null;
        }

        $payload = $jwt->decodeAndVerify($token);

        if (($payload['typ'] ?? null) !== 'access') {
            throw new \RuntimeException('Not an access token.');
        }

        $jti = (string) ($payload['jti'] ?? '');
        if ($jti === '' || $blacklistRepo->isBlacklisted($jti)) {
            throw new \RuntimeException('Token revoked or invalid.');
        }

        $email = (string) ($payload['email'] ?? '');
        if ($email === '') {
            throw new \RuntimeException('Missing email in token.');
        }

        $user = $users->findOneBy(['email' => $email]);

        return $user instanceof User ? $user : null;
    }

    #[Route('/jobs/new', name: 'web_job_new', methods: ['GET', 'POST'])]
    #[Route('/jobs/new', name: 'job_new', methods: ['GET', 'POST'])] // alias, optional
    public function new(
        Request $request,
        EntityManagerInterface $em,
        JwtService $jwt,
        UserRepository $users,
        AuthAccessTokenBlacklistRepository $blacklistRepo,
        CsrfTokenManagerInterface $csrf,
    ): Response {
        // 1) Resolve user from JWT (cookie / Authorization)
        try {
            $user = $this->resolveUserFromJwt($request, $jwt, $users, $blacklistRepo);
        } catch (\Throwable $e) {
            return $this->redirectToRoute('web_login');
        }

        if (!$user) {
            return $this->redirectToRoute('web_login');
        }

        // 2) Role check
        $roles = $user->getRoles();
        if (!\in_array(User::ROLE_HR, $roles, true) && !\in_array(User::ROLE_ADMIN, $roles, true)) {
            $this->addFlash('error', 'Only HR or Admin users can create job postings.');
            return $this->redirectToRoute('web_dashboard');
        }

        // 3) Handle POST (create job)
        if ($request->isMethod('POST')) {
            // CSRF check: ID "job_create" must match the token we generate for the form
            $submittedToken = (string) $request->request->get('_csrf_token', '');
            $tokenObj = new CsrfToken('job_create', $submittedToken);

            if (!$csrf->isTokenValid($tokenObj)) {
                $this->addFlash('error', 'Invalid form token, please try again.');
                return $this->redirectToRoute('web_job_new');
            }

            $title        = \trim((string) $request->request->get('title', ''));
            $description  = \trim((string) $request->request->get('description', ''));
            $location     = $request->request->get('location') ?: null;
            $requirements = $request->request->get('requirements') ?: null;

            // IMPORTANT: use same field names as in jobs/new.html.twig
            $workMode       = (string) $request->request->get('work_mode', Job::WORK_ONSITE);
            $employmentType = (string) $request->request->get('employment_type', Job::TYPE_FULL_TIME);

            if ($title === '' || $description === '') {
                $this->addFlash('error', 'Title and description are required.');
                return $this->redirectToRoute('web_job_new');
            }

            $membership = $user->getCompanyMemberships()->first();
            if (!$membership) {
                $this->addFlash('error', 'You must belong to a company to create jobs.');
                return $this->redirectToRoute('web_dashboard');
            }

            $job = (new Job())
                ->setCompany($membership->getCompany())
                ->setPostedBy($user)
                ->setTitle($title)
                ->setDescription($description)
                ->setLocation($location)
                ->setRequirements($requirements)
                ->setWorkMode($workMode)
                ->setEmploymentType($employmentType)
                ->setStatus(Job::STATUS_PUBLISHED)
                ->setPublishedAt(new \DateTime()); 

            $em->persist($job);
            $em->flush();

            $this->addFlash('success', \sprintf('Job "%s" published successfully.', $title));

            return $this->redirectToRoute('web_employer_jobs');
        }

        // 4) GET: render form with CSRF token
        $csrfToken = $csrf->getToken('job_create')->getValue();

        return $this->render('jobs/new.html.twig', [
            'user'       => $user,
            'csrf_token' => $csrfToken,
        ]);
    }
#[Route('/employer/jobs', name: 'web_employer_jobs', methods: ['GET'])]
public function employerJobs(
    Request $request,
    JwtService $jwt,
    UserRepository $users,
    AuthAccessTokenBlacklistRepository $blacklistRepo,
    EntityManagerInterface $em,
    CsrfTokenManagerInterface $csrf,
): Response {
    // 1) Resolve user from JWT
    try {
        $user = $this->resolveUserFromJwt($request, $jwt, $users, $blacklistRepo);
    } catch (\Throwable $e) {
        return $this->redirectToRoute('web_login');
    }

    if (!$user) {
        return $this->redirectToRoute('web_login');
    }

    // 2) Role check
    $roles = $user->getRoles();
    if (!\in_array(User::ROLE_HR, $roles, true) && !\in_array(User::ROLE_ADMIN, $roles, true)) {
        $this->addFlash('error', 'Only HR or Admin users can manage job postings.');
        return $this->redirectToRoute('web_dashboard');
    }

    // 3) Get company membership
    $membership = $user->getCompanyMemberships()->first();
    if (!$membership) {
        $this->addFlash('error', 'You must belong to a company to manage jobs.');
        return $this->redirectToRoute('web_dashboard');
    }

    $company = $membership->getCompany();

    // 4) Fetch jobs for that company
    $jobRepo = $em->getRepository(Job::class);
    /** @var Job[] $jobList */
    $jobList = $jobRepo->findBy(
        ['company' => $company],
        ['publishedAt' => 'DESC']
    );

    // 5) Generate CSRF token for actions (e.g., delete, edit)
    $csrfToken = $csrf->getToken('manage_jobs')->getValue();

    return $this->render('employer/jobs.html.twig', [
        'user'        => $user,
        'jobs'        => $jobList,
        'csrf_token'  => $csrfToken,
    ]);
}

    #[Route('/employer/jobs/{id}/edit', name: 'web_employer_job_edit', methods: ['GET', 'POST'])]
    public function editJob(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        JwtService $jwt,
        UserRepository $users,
        AuthAccessTokenBlacklistRepository $blacklistRepo,
        CsrfTokenManagerInterface $csrf,
    ): Response {
        // Auth
        try {
            $user = $this->resolveUserFromJwt($request, $jwt, $users, $blacklistRepo);
        } catch (\Throwable $e) {
            return $this->redirectToRoute('web_login');
        }
        if (!$user) {
            return $this->redirectToRoute('web_login');
        }

        $roles = $user->getRoles();
        if (!\in_array(User::ROLE_HR, $roles, true) && !\in_array(User::ROLE_ADMIN, $roles, true)) {
            $this->addFlash('error', 'Only HR or Admin users can edit job postings.');
            return $this->redirectToRoute('web_dashboard');
        }

        /** @var Job|null $job */
        $job = $em->getRepository(Job::class)->find($id);
        if (!$job) {
            $this->addFlash('error', 'Job not found.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        // Ensure job belongs to this HR's company
        $membership = $user->getCompanyMemberships()->first();
        if (!$membership || $job->getCompany()->getId() !== $membership->getCompany()->getId()) {
            $this->addFlash('error', 'You are not allowed to edit this job.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        if ($request->isMethod('POST')) {
            // Use the same CSRF id as the list: "manage_jobs"
            $submittedToken = (string) $request->request->get('_csrf_token', '');
            if (!$csrf->isTokenValid(new CsrfToken('manage_jobs', $submittedToken))) {
                $this->addFlash('error', 'Invalid form token, please try again.');
                return $this->redirectToRoute('web_employer_job_edit', ['id' => $id]);
            }

            $title        = \trim((string) $request->request->get('title', ''));
            $description  = \trim((string) $request->request->get('description', ''));
            $location     = $request->request->get('location') ?: null;
            $requirements = $request->request->get('requirements') ?: null;
            $status       = (string) $request->request->get('status', Job::STATUS_PUBLISHED);
            $workMode       = (string) $request->request->get('work_mode', Job::WORK_ONSITE);
            $employmentType = (string) $request->request->get('employment_type', Job::TYPE_FULL_TIME);

            if ($title === '' || $description === '') {
                $this->addFlash('error', 'Title and description are required.');
                return $this->redirectToRoute('web_employer_job_edit', ['id' => $id]);
            }

            $job
                ->setTitle($title)
                ->setDescription($description)
                ->setLocation($location)
                ->setRequirements($requirements)
                ->setStatus($status)
                ->setWorkMode($workMode)
                ->setEmploymentType($employmentType);

            // If setting to PUBLISHED and there is no publishedAt yet, set it
            if ($status === Job::STATUS_PUBLISHED && $job->getPublishedAt() === null) {
                $job->setPublishedAt(new \DateTime());
            }

            $em->flush();

            $this->addFlash('success', 'Job updated successfully.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        // GET: show edit form
        $csrfToken = $csrf->getToken('manage_jobs')->getValue();

        return $this->render('employer/job_edit.html.twig', [
            'user'       => $user,
            'job'        => $job,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/employer/jobs/{id}/delete', name: 'web_employer_job_delete', methods: ['POST'])]
    public function deleteJob(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        JwtService $jwt,
        UserRepository $users,
        AuthAccessTokenBlacklistRepository $blacklistRepo,
        CsrfTokenManagerInterface $csrf,
    ): Response {
        // Auth
        try {
            $user = $this->resolveUserFromJwt($request, $jwt, $users, $blacklistRepo);
        } catch (\Throwable $e) {
            return $this->redirectToRoute('web_login');
        }
        if (!$user) {
            return $this->redirectToRoute('web_login');
        }

        $roles = $user->getRoles();
        if (!\in_array(User::ROLE_HR, $roles, true) && !\in_array(User::ROLE_ADMIN, $roles, true)) {
            $this->addFlash('error', 'Only HR or Admin users can delete job postings.');
            return $this->redirectToRoute('web_dashboard');
        }

        /** @var Job|null $job */
        $job = $em->getRepository(Job::class)->find($id);
        if (!$job) {
            $this->addFlash('error', 'Job not found.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        // Ensure job belongs to this HR's company
        $membership = $user->getCompanyMemberships()->first();
        if (!$membership || $job->getCompany()->getId() !== $membership->getCompany()->getId()) {
            $this->addFlash('error', 'You are not allowed to delete this job.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        // CSRF
        $submittedToken = (string) $request->request->get('_csrf_token', '');
        if (!$csrf->isTokenValid(new CsrfToken('manage_jobs', $submittedToken))) {
            $this->addFlash('error', 'Invalid form token, please try again.');
            return $this->redirectToRoute('web_employer_jobs');
        }

        $title = $job->getTitle();

        $em->remove($job);
        $em->flush();

        $this->addFlash('success', \sprintf('Job "%s" deleted successfully.', $title));

        return $this->redirectToRoute('web_employer_jobs');
    }

}