<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527002400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE telegram_user_id_seq CASCADE');
        $this->addSql('DROP TABLE telegram_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE telegram_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE telegram_user (
          id SERIAL NOT NULL,
          telegram_id BIGINT NOT NULL,
          username VARCHAR(255) NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(255) DEFAULT NULL,
          phone_number VARCHAR(16) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_f180f0596b01bc5b ON telegram_user (phone_number)');
        $this->addSql('CREATE UNIQUE INDEX uniq_f180f059f85e0677 ON telegram_user (username)');
        $this->addSql('CREATE UNIQUE INDEX uniq_f180f059cc0b3066 ON telegram_user (telegram_id)');
    }
}
