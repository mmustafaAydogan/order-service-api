<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260423200506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quote_items (id INT AUTO_INCREMENT NOT NULL, qty INT NOT NULL, price DOUBLE PRECISION NOT NULL, row_total DOUBLE PRECISION NOT NULL, quote_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_ECE1642CDB805178 (quote_id), INDEX IDX_ECE1642C4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quotes (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, subtotal DOUBLE PRECISION DEFAULT 0 NOT NULL, discount_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, shipping_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, grand_total DOUBLE PRECISION DEFAULT 0 NOT NULL, applied_campaign_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quote_items ADD CONSTRAINT FK_ECE1642CDB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id)');
        $this->addSql('ALTER TABLE quote_items ADD CONSTRAINT FK_ECE1642C4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote_items DROP FOREIGN KEY FK_ECE1642CDB805178');
        $this->addSql('ALTER TABLE quote_items DROP FOREIGN KEY FK_ECE1642C4584665A');
        $this->addSql('DROP TABLE quote_items');
        $this->addSql('DROP TABLE quotes');
    }
}
