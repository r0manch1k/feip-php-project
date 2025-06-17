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
        $this->addSql('ALTER TABLE booking ADD telegram_bot_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE booking ALTER user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEE81A3623 FOREIGN KEY (telegram_bot_user_id) REFERENCES telegram_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E00CEDDEE81A3623 ON booking (telegram_bot_user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDEE81A3623');
        $this->addSql('DROP INDEX IDX_E00CEDDEE81A3623');
        $this->addSql('ALTER TABLE booking DROP telegram_bot_user_id');
        $this->addSql('ALTER TABLE booking ALTER user_id SET NOT NULL');
    }
}
