<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614083402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, category_id INT NOT NULL, owner_id INT NOT NULL, name VARCHAR(128) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(9, 2) NOT NULL, total INT NOT NULL, total_reserved INT NOT NULL, sku VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD7E3C61F9 ON product (owner_id)');
        $this->addSql('CREATE TABLE product_product_property (product_id INT NOT NULL, product_property_id INT NOT NULL, PRIMARY KEY(product_id, product_property_id))');
        $this->addSql('CREATE INDEX IDX_CECE12B54584665A ON product_product_property (product_id)');
        $this->addSql('CREATE INDEX IDX_CECE12B5F8BD8DF3 ON product_product_property (product_property_id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_product_property ADD CONSTRAINT FK_CECE12B54584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_product_property ADD CONSTRAINT FK_CECE12B5F8BD8DF3 FOREIGN KEY (product_property_id) REFERENCES product_property (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD7E3C61F9');
        $this->addSql('ALTER TABLE product_product_property DROP CONSTRAINT FK_CECE12B54584665A');
        $this->addSql('ALTER TABLE product_product_property DROP CONSTRAINT FK_CECE12B5F8BD8DF3');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_product_property');
    }
}
