<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424194453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items CHANGE price price NUMERIC(10, 4) NOT NULL, CHANGE row_total row_total NUMERIC(10, 4) NOT NULL, CHANGE discount_amount discount_amount NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE orders ADD sequence_id INT NOT NULL, CHANGE subtotal subtotal NUMERIC(20, 4) NOT NULL, CHANGE discount_amount discount_amount NUMERIC(20, 4) NOT NULL, CHANGE shipping_amount shipping_amount NUMERIC(20, 4) NOT NULL, CHANGE grand_total grand_total NUMERIC(20, 4) NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE98FB19AE FOREIGN KEY (sequence_id) REFERENCES order_sequence (sequence_value)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E52FFDEE98FB19AE ON orders (sequence_id)');
        $this->addSql('CREATE INDEX idx_orders_customer_created ON orders (customer_id, created_at)');
        $this->addSql('ALTER TABLE products CHANGE list_price list_price NUMERIC(10, 4) NOT NULL');
        $this->addSql('ALTER TABLE quote_items CHANGE price price NUMERIC(10, 4) NOT NULL, CHANGE row_total row_total NUMERIC(10, 4) NOT NULL, CHANGE discount_amount discount_amount NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE discount_amount discount_amount NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE shipping_amount shipping_amount NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE grand_total grand_total NUMERIC(10, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('CREATE INDEX idx_quotes_customer_id ON quotes (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE98FB19AE');
        $this->addSql('DROP INDEX UNIQ_E52FFDEE98FB19AE ON orders');
        $this->addSql('DROP INDEX idx_orders_customer_created ON orders');
        $this->addSql('ALTER TABLE orders DROP sequence_id, CHANGE subtotal subtotal DOUBLE PRECISION NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE order_items CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE row_total row_total DOUBLE PRECISION NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE products CHANGE list_price list_price DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX idx_quotes_customer_id ON quotes');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE quote_items CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE row_total row_total DOUBLE PRECISION NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION NOT NULL');
    }
}
