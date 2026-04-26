<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424121249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items ADD discount_amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE quote_items ADD discount_amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP discount_amount');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE quote_items DROP discount_amount');
    }
}
