<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250417180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product table and add user relationship';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_D34A04ADA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product');
    }
}