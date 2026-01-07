<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration corrigée pour supprimer l'index uq_match_app_algo
 * en gérant proprement la foreign key fk_match_application.
 */
final class Version20260106102010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update match_results schema and fix foreign key before dropping index';
    }

    public function up(Schema $schema): void
    {
        // 1. Supprimer la foreign key qui dépend de l'index
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY fk_match_application');

        // 2. Supprimer l'index unique (qui ne peut pas être supprimé tant que la FK existe)
        $this->addSql('DROP INDEX uq_match_app_algo ON match_results');

        // 3. Appliquer les changements de structure
        $this->addSql('ALTER TABLE jobs CHANGE description description LONGTEXT');
        $this->addSql('ALTER TABLE match_results 
            ADD engine_name VARCHAR(100) NOT NULL, 
            ADD decision VARCHAR(50) NOT NULL, 
            ADD overall_score INT NOT NULL, 
            ADD raw_payload JSON DEFAULT NULL, 
            DROP algorithm_version, 
            DROP match_score, 
            DROP notes, 
            CHANGE matched_keywords scores JSON DEFAULT NULL, 
            CHANGE computed_at created_at DATETIME NOT NULL');

        // 4. Recréer la foreign key (seulement sur application_id → applications.id)
        $this->addSql('ALTER TABLE match_results 
            ADD CONSTRAINT fk_match_application 
            FOREIGN KEY (application_id) REFERENCES applications (id)');
    }

    public function down(Schema $schema): void
    {
        // 1. Supprimer la nouvelle foreign key
        $this->addSql('ALTER TABLE match_results DROP FOREIGN KEY fk_match_application');

        // 2. Revenir à l'ancienne structure
        $this->addSql('ALTER TABLE jobs CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE match_results 
            ADD algorithm_version VARCHAR(50) DEFAULT \'v1\' NOT NULL, 
            ADD match_score NUMERIC(5,2) NOT NULL, 
            ADD matched_keywords JSON DEFAULT NULL, 
            ADD notes LONGTEXT DEFAULT NULL, 
            DROP engine_name, 
            DROP decision, 
            DROP overall_score, 
            DROP raw_payload, 
            CHANGE created_at computed_at DATETIME NOT NULL');

        // 3. Recréer l'index unique
        $this->addSql('CREATE UNIQUE INDEX uq_match_app_algo ON match_results (application_id, algorithm_version)');

        // 4. Recréer la foreign key (compatible avec l'index)
        $this->addSql('ALTER TABLE match_results 
            ADD CONSTRAINT fk_match_application 
            FOREIGN KEY (application_id) REFERENCES applications (id)');
    }
}