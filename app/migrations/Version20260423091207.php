<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260423091207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authors (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, category_title VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, list_price NUMERIC(10, 2) NOT NULL, stock_quantity INT NOT NULL, category_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_B3BA5A5A12469DE2 (category_id), INDEX IDX_B3BA5A5AF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AF675F31B FOREIGN KEY (author_id) REFERENCES authors (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AF675F31B');
        $this->addSql('DROP TABLE authors');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE products');
    }
}
