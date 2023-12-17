<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231216173423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE product_bill_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE bill DROP products_info_at_creation');
        $this->addSql('ALTER TABLE product_bill DROP CONSTRAINT fk_f7c93b424584665a');
        $this->addSql('ALTER TABLE product_bill DROP CONSTRAINT fk_f7c93b421a8c12f5');
        $this->addSql('DROP INDEX idx_f7c93b421a8c12f5');
        $this->addSql('DROP INDEX idx_f7c93b424584665a');
        $this->addSql('ALTER TABLE product_bill DROP CONSTRAINT product_bill_pkey');
        $this->addSql('ALTER TABLE product_bill ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD product_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD bill_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD product_price_at_bill_creation DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD product_name_at_bill_creation VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD product_description_at_bill_creation TEXT NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD product_quantity INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill DROP product_id');
        $this->addSql('ALTER TABLE product_bill DROP bill_id');
        $this->addSql('ALTER TABLE product_bill ADD CONSTRAINT FK_F7C93B42DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_bill ADD CONSTRAINT FK_F7C93B4249B4CBC9 FOREIGN KEY (bill_id_id) REFERENCES bill (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F7C93B42DE18E50B ON product_bill (product_id_id)');
        $this->addSql('CREATE INDEX IDX_F7C93B4249B4CBC9 ON product_bill (bill_id_id)');
        $this->addSql('ALTER TABLE product_bill ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE "user" ALTER company_id_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE product_bill_id_seq CASCADE');
        $this->addSql('ALTER TABLE "user" ALTER company_id_id SET NOT NULL');
        $this->addSql('ALTER TABLE bill ADD products_info_at_creation JSON NOT NULL');
        $this->addSql('ALTER TABLE product_bill DROP CONSTRAINT FK_F7C93B42DE18E50B');
        $this->addSql('ALTER TABLE product_bill DROP CONSTRAINT FK_F7C93B4249B4CBC9');
        $this->addSql('DROP INDEX IDX_F7C93B42DE18E50B');
        $this->addSql('DROP INDEX IDX_F7C93B4249B4CBC9');
        $this->addSql('DROP INDEX product_bill_pkey');
        $this->addSql('ALTER TABLE product_bill ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill ADD bill_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_bill DROP id');
        $this->addSql('ALTER TABLE product_bill DROP product_id_id');
        $this->addSql('ALTER TABLE product_bill DROP bill_id_id');
        $this->addSql('ALTER TABLE product_bill DROP product_price_at_bill_creation');
        $this->addSql('ALTER TABLE product_bill DROP product_name_at_bill_creation');
        $this->addSql('ALTER TABLE product_bill DROP product_description_at_bill_creation');
        $this->addSql('ALTER TABLE product_bill DROP product_quantity');
        $this->addSql('ALTER TABLE product_bill ADD CONSTRAINT fk_f7c93b424584665a FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_bill ADD CONSTRAINT fk_f7c93b421a8c12f5 FOREIGN KEY (bill_id) REFERENCES bill (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f7c93b421a8c12f5 ON product_bill (bill_id)');
        $this->addSql('CREATE INDEX idx_f7c93b424584665a ON product_bill (product_id)');
        $this->addSql('ALTER TABLE product_bill ADD PRIMARY KEY (product_id, bill_id)');
    }
}
