<?php
declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\User;
use App\Entity\Company;
use App\Entity\CompanyMember;
use App\Repository\UserRepository;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use App\Security\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AdminController extends AbstractController
{
    private const CSRF_INTENTION = 'admin_global';

    public function __construct(
        private readonly UserRepository $users,
        private readonly CompanyRepository $companies,
        private readonly JobRepository $jobs,
        private readonly JwtService $jwt,
        private readonly CsrfTokenManagerInterface $csrf,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/admin', name: 'web_admin_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $admin = $this->resolveAdmin($request);
        if (!$admin) {
            return $this->redirectToRoute('web_login');
        }

        $csrfToken = $this->csrf->getToken(self::CSRF_INTENTION)->getValue();

        return $this->render('admin/index.html.twig', [
            'user'          => $admin,
            'csrf_token'    => $csrfToken,
            'user_count'    => $this->users->count([]),
            'company_count' => $this->companies->count([]),
            'job_count'     => $this->jobs->count([]),
        ]);
    }

    #[Route('/admin/users', name: 'web_admin_users', methods: ['GET'])]
    public function users(Request $request): Response
    {
        $admin = $this->resolveAdmin($request);
        if (!$admin) {
            return $this->redirectToRoute('web_login');
        }

        $csrfToken = $this->csrf->getToken(self::CSRF_INTENTION)->getValue();
        $allUsers  = $this->users->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/users.html.twig', [
            'user'       => $admin,
            'users'      => $allUsers,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/admin/companies', name: 'web_admin_companies', methods: ['GET'])]
    public function companies(Request $request): Response
    {
        $admin = $this->resolveAdmin($request);
        if (!$admin) {
            return $this->redirectToRoute('web_login');
        }

        $csrfToken = $this->csrf->getToken(self::CSRF_INTENTION)->getValue();
        $companies = $this->companies->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/companies.html.twig', [
            'user'       => $admin,
            'companies'  => $companies,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/admin/jobs', name: 'web_admin_jobs', methods: ['GET'])]
    public function jobs(Request $request): Response
    {
        $admin = $this->resolveAdmin($request);
        if (!$admin) {
            return $this->redirectToRoute('web_login');
        }

        $csrfToken = $this->csrf->getToken(self::CSRF_INTENTION)->getValue();
        $jobs      = $this->jobs->findBy([], ['publishedAt' => 'DESC']);

        return $this->render('admin/jobs.html.twig', [
            'user'       => $admin,
            'jobs'       => $jobs,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/admin/companies/new', name: 'web_admin_companies_new', methods: ['GET', 'POST'])]
    public function newCompany(Request $request): Response
    {
        $admin = $this->resolveAdmin($request);
        if (!$admin) {
            return $this->redirectToRoute('web_login');
        }

        // Only full admins can access this
        if (!in_array(User::ROLE_ADMIN, $admin->getRoles(), true)) {
            $this->addFlash('error', 'Only Admin users can create companies.');
            return $this->redirectToRoute('web_dashboard');
        }

        if ($request->isMethod('GET')) {
            return $this->render('admin/company_new.html.twig', [
                'user'       => $admin,
                'csrf_token' => $this->csrf->getToken(self::CSRF_INTENTION)->getValue(),
            ]);
        }

        // POST: handle form submission
        $submittedToken = (string) $request->request->get('_csrf_token', '');
        if (!$this->csrf->isTokenValid(new CsrfToken(self::CSRF_INTENTION, $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('web_admin_companies_new');
        }

        $name     = trim((string) $request->request->get('name', ''));
        $website  = trim((string) $request->request->get('website', ''));
        $industry = trim((string) $request->request->get('industry', ''));
        $hrEmail  = strtolower(trim((string) $request->request->get('hr_email', '')));

        if ($name === '') {
            $this->addFlash('error', 'Company name is required.');
            return $this->redirectToRoute('web_admin_companies_new');
        }

        // Create company
        $company = (new Company())
            ->setName($name)
            ->setWebsite($website !== '' ? $website : null)
            ->setIndustry($industry !== '' ? $industry : null);

        $this->em->persist($company);

        // Optional: first HR member
        if ($hrEmail !== '') {
            $hrUser = $this->users->findOneBy(['email' => $hrEmail]);
            if ($hrUser) {
                // ensure user has ROLE_HR
                $roles = $hrUser->getRoles();
                if (!in_array(User::ROLE_HR, $roles, true)) {
                    $roles[] = User::ROLE_HR;
                    $hrUser->setRoles($roles);
                    $this->em->persist($hrUser);
                }

                $member = (new CompanyMember())
                    ->setCompany($company)
                    ->setUser($hrUser)
                    ->setRoleInCompany(CompanyMember::ROLE_HR);

                $this->em->persist($member);
            } else {
                $this->addFlash('error', sprintf('No user found with email "%s" to assign as HR.', $hrEmail));
            }
        }

        $this->em->flush();

        $this->addFlash('success', sprintf('Company "%s" created successfully.', $company->getName()));

        return $this->redirectToRoute('web_admin_companies');
    }

    /**
     * Resolve current admin user from Symfony security or JWT cookie.
     */
    private function resolveAdmin(Request $request): ?User
    {
        $user = $this->getUser();
        if ($user instanceof User && in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return $user;
        }

        // Fallback: decode access_token cookie
        $raw = (string) $request->cookies->get('access_token', '');
        if ($raw === '') {
            return null;
        }

        try {
            $payload = $this->jwt->decodeAndVerify($raw);
        } catch (\Throwable $e) {
            return null;
        }

        $email = (string) ($payload['email'] ?? '');
        if ($email === '') {
            return null;
        }

        $entity = $this->users->findOneBy(['email' => $email]);
        if (!$entity instanceof User) {
            return null;
        }

        if (!in_array(User::ROLE_ADMIN, $entity->getRoles(), true)) {
            return null;
        }

        return $entity;
    }
}
