<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531063746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDEA76ED395');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP INDEX UNIQ_67D5399DD4E6F81');
        $this->addSql('DROP INDEX IDX_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE booking ADD phone_number VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE booking DROP user_id');
    }
}
