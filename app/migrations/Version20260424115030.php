<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424115030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_sequence (sequence_value INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (sequence_value)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_sequence');
        $this->addSql('ALTER TABLE quotes CHANGE subtotal subtotal DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE discount_amount discount_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE shipping_amount shipping_amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE grand_total grand_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
