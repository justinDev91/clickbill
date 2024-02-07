<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207153244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bill ADD guid UUID NOT NULL');
        $this->addSql('ALTER TABLE bill ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE bill ALTER created_by DROP NOT NULL');
        $this->addSql('ALTER TABLE bill ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE bill ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE bill ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE bill RENAME COLUMN quantity TO quote_id');
        $this->addSql('ALTER TABLE bill RENAME COLUMN total_amount TO amount');
        $this->addSql('ALTER TABLE bill ADD CONSTRAINT FK_7A2119E3DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A2119E32B6FCFB2 ON bill (guid)');
        $this->addSql('CREATE INDEX IDX_7A2119E3DB805178 ON bill (quote_id)');
        $this->addSql('ALTER TABLE client ADD slug VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE client ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE client ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE client ALTER updated_at SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455989D9B62 ON client (slug)');
        $this->addSql('ALTER TABLE quote ALTER down_payment DROP NOT NULL');
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
        $this->addSql('ALTER TABLE quote ALTER down_payment SET NOT NULL');
        $this->addSql('ALTER TABLE bill DROP CONSTRAINT FK_7A2119E3DB805178');
        $this->addSql('DROP INDEX UNIQ_7A2119E32B6FCFB2');
        $this->addSql('DROP INDEX IDX_7A2119E3DB805178');
        $this->addSql('ALTER TABLE bill DROP guid');
        $this->addSql('ALTER TABLE bill ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE bill ALTER created_by SET NOT NULL');
        $this->addSql('ALTER TABLE bill ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE bill ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE bill ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE bill RENAME COLUMN quote_id TO quantity');
        $this->addSql('ALTER TABLE bill RENAME COLUMN amount TO total_amount');
    }
}
