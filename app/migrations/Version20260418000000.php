<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create product_image table for additional product images
 */
final class Version20260418000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product_image table for additional product images';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_image (
            id INT AUTO_INCREMENT NOT NULL,
            product_id INT NOT NULL,
            image VARCHAR(255) NOT NULL,
            image_file_id VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            INDEX idx_product_image_product_id (product_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_product_image_product FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_image');
    }
}
