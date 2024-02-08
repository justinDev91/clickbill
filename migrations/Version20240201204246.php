<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201204246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD slug VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE client ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE client ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE client ALTER updated_at SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455989D9B62 ON client (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_C7440455989D9B62');
        $this->addSql('ALTER TABLE client DROP slug');
        $this->addSql('ALTER TABLE client ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE client ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE client ALTER updated_at DROP NOT NULL');
    }
}
