<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107091053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applications CHANGE status status VARCHAR(30) DEFAULT \'SUBMITTED\' NOT NULL, CHANGE cover_letter cover_letter LONGTEXT DEFAULT NULL, CHANGE applied_at applied_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE interview_notes interview_notes LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F0BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F028BF7E34 FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F0D262AF09 FOREIGN KEY (resume_id) REFERENCES resumes (id) ON DELETE RESTRICT');
        $this->addSql('DROP INDEX idx_app_job ON applications');
        $this->addSql('CREATE INDEX IDX_F7C966F0BE04EA9 ON applications (job_id)');
        $this->addSql('DROP INDEX idx_app_candidate ON applications');
        $this->addSql('CREATE INDEX IDX_F7C966F028BF7E34 ON applications (candidate_user_id)');
        $this->addSql('DROP INDEX fk_app_resume ON applications');
        $this->addSql('CREATE INDEX IDX_F7C966F0D262AF09 ON applications (resume_id)');
        $this->addSql('ALTER TABLE auth_access_token_blacklist DROP FOREIGN KEY `fk_blacklist_user`');
        $this->addSql('DROP INDEX idx_blacklist_exp ON auth_access_token_blacklist');
        $this->addSql('ALTER TABLE auth_access_token_blacklist DROP FOREIGN KEY `fk_blacklist_user`');
        $this->addSql('ALTER TABLE auth_access_token_blacklist CHANGE jti jti VARCHAR(36) NOT NULL, CHANGE revoked_at revoked_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE auth_access_token_blacklist ADD CONSTRAINT FK_C469F722A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_blacklist_user ON auth_access_token_blacklist');
        $this->addSql('CREATE INDEX IDX_C469F722A76ED395 ON auth_access_token_blacklist (user_id)');
        $this->addSql('ALTER TABLE auth_access_token_blacklist ADD CONSTRAINT `fk_blacklist_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_refresh_tokens DROP FOREIGN KEY `fk_refresh_user`');
        $this->addSql('DROP INDEX idx_refresh_exp ON auth_refresh_tokens');
        $this->addSql('ALTER TABLE auth_refresh_tokens DROP FOREIGN KEY `fk_refresh_user`');
        $this->addSql('ALTER TABLE auth_refresh_tokens CHANGE token_hash token_hash VARCHAR(64) NOT NULL, CHANGE issued_at issued_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE auth_refresh_tokens ADD CONSTRAINT FK_861C6459A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_refresh_user ON auth_refresh_tokens');
        $this->addSql('CREATE INDEX IDX_861C6459A76ED395 ON auth_refresh_tokens (user_id)');
        $this->addSql('ALTER TABLE auth_refresh_tokens ADD CONSTRAINT `fk_refresh_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidate_profiles DROP FOREIGN KEY `fk_candidate_user`');
        $this->addSql('ALTER TABLE candidate_profiles CHANGE summary summary LONGTEXT DEFAULT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE candidate_profiles ADD CONSTRAINT FK_2A6EC7E3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE companies CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY `fk_member_company`');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY `fk_member_user`');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY `fk_member_user`');
        $this->addSql('ALTER TABLE company_members CHANGE role_in_company role_in_company VARCHAR(20) DEFAULT \'HR\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT FK_65F2C828979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT FK_65F2C828A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_company_members_user ON company_members');
        $this->addSql('CREATE INDEX IDX_65F2C828A76ED395 ON company_members (user_id)');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT `fk_member_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY `fk_jobs_company`');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY `fk_jobs_posted_by`');
        $this->addSql('DROP INDEX idx_jobs_status_pub ON jobs');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY `fk_jobs_company`');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY `fk_jobs_posted_by`');
        $this->addSql('ALTER TABLE jobs DROP image_name, CHANGE work_mode work_mode VARCHAR(20) DEFAULT \'ONSITE\' NOT NULL, CHANGE employment_type employment_type VARCHAR(20) DEFAULT \'FULL_TIME\' NOT NULL, CHANGE description description LONGTEXT, CHANGE status status VARCHAR(20) DEFAULT \'DRAFT\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC512CA0262 FOREIGN KEY (posted_by_user_id) REFERENCES users (id) ON DELETE RESTRICT');
        $this->addSql('DROP INDEX idx_jobs_company ON jobs');
        $this->addSql('CREATE INDEX IDX_A8936DC5979B1AD6 ON jobs (company_id)');
        $this->addSql('DROP INDEX idx_jobs_posted_by ON jobs');
        $this->addSql('CREATE INDEX IDX_A8936DC512CA0262 ON jobs (posted_by_user_id)');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT `fk_jobs_company` FOREIGN KEY (company_id) REFERENCES companies (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT `fk_jobs_posted_by` FOREIGN KEY (posted_by_user_id) REFERENCES users (id) ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY `fk_match_application`');
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY `fk_match_application`');
        $this->addSql('ALTER TABLE match_results ADD CONSTRAINT FK_E805BB7B3E030ACD FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX fk_match_application ON match_results');
        $this->addSql('CREATE INDEX IDX_E805BB7B3E030ACD ON match_results (application_id)');
        $this->addSql('ALTER TABLE match_results ADD CONSTRAINT `fk_match_application` FOREIGN KEY (application_id) REFERENCES applications (id)');
        $this->addSql('ALTER TABLE resumes DROP FOREIGN KEY `fk_resume_candidate`');
        $this->addSql('DROP INDEX idx_resume_default ON resumes');
        $this->addSql('ALTER TABLE resumes DROP FOREIGN KEY `fk_resume_candidate`');
        $this->addSql('ALTER TABLE resumes CHANGE sha256 sha256 VARCHAR(64) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE resumes ADD CONSTRAINT FK_CDB8AD3328BF7E34 FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_resume_candidate ON resumes');
        $this->addSql('CREATE INDEX IDX_CDB8AD3328BF7E34 ON resumes (candidate_user_id)');
        $this->addSql('ALTER TABLE resumes ADD CONSTRAINT `fk_resume_candidate` FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users CHANGE status status VARCHAR(20) DEFAULT \'ACTIVE\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F0BE04EA9');
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F028BF7E34');
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F0D262AF09');
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F0BE04EA9');
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F028BF7E34');
        $this->addSql('ALTER TABLE applications DROP FOREIGN KEY FK_F7C966F0D262AF09');
        $this->addSql('ALTER TABLE applications CHANGE status status ENUM(\'SUBMITTED\', \'IN_REVIEW\', \'SHORTLISTED\', \'REJECTED\', \'HIRED\', \'WITHDRAWN\') DEFAULT \'SUBMITTED\' NOT NULL, CHANGE cover_letter cover_letter TEXT DEFAULT NULL, CHANGE applied_at applied_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE interview_notes interview_notes TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_f7c966f0d262af09 ON applications');
        $this->addSql('CREATE INDEX fk_app_resume ON applications (resume_id)');
        $this->addSql('DROP INDEX idx_f7c966f028bf7e34 ON applications');
        $this->addSql('CREATE INDEX idx_app_candidate ON applications (candidate_user_id)');
        $this->addSql('DROP INDEX idx_f7c966f0be04ea9 ON applications');
        $this->addSql('CREATE INDEX idx_app_job ON applications (job_id)');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F0BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F028BF7E34 FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applications ADD CONSTRAINT FK_F7C966F0D262AF09 FOREIGN KEY (resume_id) REFERENCES resumes (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE auth_access_token_blacklist DROP FOREIGN KEY FK_C469F722A76ED395');
        $this->addSql('ALTER TABLE auth_access_token_blacklist DROP FOREIGN KEY FK_C469F722A76ED395');
        $this->addSql('ALTER TABLE auth_access_token_blacklist CHANGE jti jti CHAR(36) NOT NULL, CHANGE revoked_at revoked_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE auth_access_token_blacklist ADD CONSTRAINT `fk_blacklist_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_blacklist_exp ON auth_access_token_blacklist (expires_at)');
        $this->addSql('DROP INDEX idx_c469f722a76ed395 ON auth_access_token_blacklist');
        $this->addSql('CREATE INDEX idx_blacklist_user ON auth_access_token_blacklist (user_id)');
        $this->addSql('ALTER TABLE auth_access_token_blacklist ADD CONSTRAINT FK_C469F722A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_refresh_tokens DROP FOREIGN KEY FK_861C6459A76ED395');
        $this->addSql('ALTER TABLE auth_refresh_tokens DROP FOREIGN KEY FK_861C6459A76ED395');
        $this->addSql('ALTER TABLE auth_refresh_tokens CHANGE token_hash token_hash CHAR(64) NOT NULL, CHANGE issued_at issued_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE auth_refresh_tokens ADD CONSTRAINT `fk_refresh_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_refresh_exp ON auth_refresh_tokens (expires_at)');
        $this->addSql('DROP INDEX idx_861c6459a76ed395 ON auth_refresh_tokens');
        $this->addSql('CREATE INDEX idx_refresh_user ON auth_refresh_tokens (user_id)');
        $this->addSql('ALTER TABLE auth_refresh_tokens ADD CONSTRAINT FK_861C6459A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidate_profiles DROP FOREIGN KEY FK_2A6EC7E3A76ED395');
        $this->addSql('ALTER TABLE candidate_profiles CHANGE summary summary TEXT DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE candidate_profiles ADD CONSTRAINT `fk_candidate_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE companies CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY FK_65F2C828979B1AD6');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY FK_65F2C828A76ED395');
        $this->addSql('ALTER TABLE company_members DROP FOREIGN KEY FK_65F2C828A76ED395');
        $this->addSql('ALTER TABLE company_members CHANGE role_in_company role_in_company ENUM(\'HR\', \'COMPANY_ADMIN\') DEFAULT \'HR\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT `fk_member_company` FOREIGN KEY (company_id) REFERENCES companies (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT `fk_member_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_65f2c828a76ed395 ON company_members');
        $this->addSql('CREATE INDEX idx_company_members_user ON company_members (user_id)');
        $this->addSql('ALTER TABLE company_members ADD CONSTRAINT FK_65F2C828A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC5979B1AD6');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC512CA0262');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC5979B1AD6');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC512CA0262');
        $this->addSql('ALTER TABLE jobs ADD image_name VARCHAR(255) DEFAULT NULL, CHANGE work_mode work_mode ENUM(\'ONSITE\', \'HYBRID\', \'REMOTE\') DEFAULT \'ONSITE\' NOT NULL, CHANGE employment_type employment_type ENUM(\'FULL_TIME\', \'PART_TIME\', \'CONTRACT\', \'INTERN\', \'TEMP\') DEFAULT \'FULL_TIME\' NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE status status ENUM(\'DRAFT\', \'PUBLISHED\', \'CLOSED\') DEFAULT \'DRAFT\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT `fk_jobs_company` FOREIGN KEY (company_id) REFERENCES companies (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT `fk_jobs_posted_by` FOREIGN KEY (posted_by_user_id) REFERENCES users (id) ON UPDATE CASCADE');
        $this->addSql('CREATE INDEX idx_jobs_status_pub ON jobs (status, published_at)');
        $this->addSql('DROP INDEX idx_a8936dc512ca0262 ON jobs');
        $this->addSql('CREATE INDEX idx_jobs_posted_by ON jobs (posted_by_user_id)');
        $this->addSql('DROP INDEX idx_a8936dc5979b1ad6 ON jobs');
        $this->addSql('CREATE INDEX idx_jobs_company ON jobs (company_id)');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC512CA0262 FOREIGN KEY (posted_by_user_id) REFERENCES users (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY FK_E805BB7B3E030ACD');
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY FK_E805BB7B3E030ACD');
        $this->addSql('ALTER TABLE match_results ADD CONSTRAINT `fk_match_application` FOREIGN KEY (application_id) REFERENCES applications (id)');
        $this->addSql('DROP INDEX idx_e805bb7b3e030acd ON match_results');
        $this->addSql('CREATE INDEX fk_match_application ON match_results (application_id)');
        $this->addSql('ALTER TABLE match_results ADD CONSTRAINT FK_E805BB7B3E030ACD FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resumes DROP FOREIGN KEY FK_CDB8AD3328BF7E34');
        $this->addSql('ALTER TABLE resumes DROP FOREIGN KEY FK_CDB8AD3328BF7E34');
        $this->addSql('ALTER TABLE resumes CHANGE sha256 sha256 CHAR(64) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE resumes ADD CONSTRAINT `fk_resume_candidate` FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_resume_default ON resumes (candidate_user_id, is_default)');
        $this->addSql('DROP INDEX idx_cdb8ad3328bf7e34 ON resumes');
        $this->addSql('CREATE INDEX idx_resume_candidate ON resumes (candidate_user_id)');
        $this->addSql('ALTER TABLE resumes ADD CONSTRAINT FK_CDB8AD3328BF7E34 FOREIGN KEY (candidate_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users CHANGE roles roles JSON DEFAULT \'["ROLE_CANDIDATE"]\' NOT NULL, CHANGE status status ENUM(\'ACTIVE\', \'SUSPENDED\') DEFAULT \'ACTIVE\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }
}
