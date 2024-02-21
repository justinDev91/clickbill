<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221141135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD display_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C7440455979B1AD6 ON client (company_id)');
        $this->addSql('ALTER TABLE client_interaction ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE client_interaction ADD action VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE client_interaction ADD CONSTRAINT FK_36C6D89C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_36C6D89C979B1AD6 ON client_interaction (company_id)');
        $this->addSql('ALTER TABLE company ALTER logo DROP NOT NULL');
        $this->addSql('ALTER TABLE quote ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE quote ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE quote ALTER updated_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quote ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE quote ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE quote ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE company ALTER logo SET NOT NULL');
        $this->addSql('ALTER TABLE client_interaction DROP CONSTRAINT FK_36C6D89C979B1AD6');
        $this->addSql('DROP INDEX IDX_36C6D89C979B1AD6');
        $this->addSql('ALTER TABLE client_interaction DROP company_id');
        $this->addSql('ALTER TABLE client_interaction DROP action');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455979B1AD6');
        $this->addSql('DROP INDEX IDX_C7440455979B1AD6');
        $this->addSql('ALTER TABLE client DROP company_id');
        $this->addSql('ALTER TABLE client DROP display_name');
    }
}
