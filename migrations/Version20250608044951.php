<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608044951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking RENAME TO bookings');
        $this->addSql('ALTER TABLE house RENAME TO houses');
        $this->addSql('ALTER TABLE summer_house RENAME TO summer_houses');
        $this->addSql('ALTER TABLE telegram_user RENAME TO telegram_users');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings RENAME TO booking');
        $this->addSql('ALTER TABLE houses RENAME TO house');
        $this->addSql('ALTER TABLE summer_houses RENAME TO summer_house');
        $this->addSql('ALTER TABLE telegram_users RENAME TO telegram_user');
    }
}
