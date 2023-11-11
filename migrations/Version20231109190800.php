<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231109190800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER display_name SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER last_login TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".last_login IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ALTER display_name DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER last_login TYPE DATE');
        $this->addSql('ALTER TABLE "user" ALTER created_at TYPE DATE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at TYPE DATE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".last_login IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS NULL');
    }
}
