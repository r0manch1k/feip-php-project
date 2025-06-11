<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606063219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD telegram_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE booking ALTER user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT FK_E00CEDDEFC28B263 FOREIGN KEY (telegram_user_id) REFERENCES telegram_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E00CEDDEFC28B263 ON booking (telegram_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDEFC28B263');
        $this->addSql('DROP INDEX IDX_E00CEDDEFC28B263');
        $this->addSql('ALTER TABLE booking DROP telegram_user_id');
        $this->addSql('ALTER TABLE booking ALTER user_id SET NOT NULL');
    }
}
