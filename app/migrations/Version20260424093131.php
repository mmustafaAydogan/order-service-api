<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424093131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, product_title VARCHAR(255) NOT NULL, qty INT NOT NULL, price DOUBLE PRECISION NOT NULL, row_total DOUBLE PRECISION NOT NULL, order_id INT NOT NULL, INDEX IDX_62809DB08D9F6D38 (order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, subtotal DOUBLE PRECISION NOT NULL, discount_amount DOUBLE PRECISION NOT NULL, shipping_amount DOUBLE PRECISION NOT NULL, grand_total DOUBLE PRECISION NOT NULL, applied_campaign_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
