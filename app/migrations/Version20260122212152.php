<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122212152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_children (category_source INT NOT NULL, category_target INT NOT NULL, INDEX IDX_16ED35C15062B508 (category_source), INDEX IDX_16ED35C14987E587 (category_target), PRIMARY KEY (category_source, category_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE category_children ADD CONSTRAINT FK_16ED35C15062B508 FOREIGN KEY (category_source) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_children ADD CONSTRAINT FK_16ED35C14987E587 FOREIGN KEY (category_target) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_parent DROP FOREIGN KEY `FK_3266AA344987E587`');
        $this->addSql('ALTER TABLE category_parent DROP FOREIGN KEY `FK_3266AA345062B508`');
        $this->addSql('DROP TABLE category_parent');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_parent (category_source INT NOT NULL, category_target INT NOT NULL, INDEX IDX_3266AA344987E587 (category_target), INDEX IDX_3266AA345062B508 (category_source), PRIMARY KEY (category_source, category_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE category_parent ADD CONSTRAINT `FK_3266AA344987E587` FOREIGN KEY (category_target) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_parent ADD CONSTRAINT `FK_3266AA345062B508` FOREIGN KEY (category_source) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_children DROP FOREIGN KEY FK_16ED35C15062B508');
        $this->addSql('ALTER TABLE category_children DROP FOREIGN KEY FK_16ED35C14987E587');
        $this->addSql('DROP TABLE category_children');
    }
}
