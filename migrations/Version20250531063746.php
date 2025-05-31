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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE refresh_tokens (
          id SERIAL NOT NULL,
          refresh_token VARCHAR(128) NOT NULL,
          username VARCHAR(255) NOT NULL,
          valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE "user" (
          id SERIAL NOT NULL,
          phone_number VARCHAR(16) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496B01BC5B ON "user" (phone_number)');
        $this->addSql('ALTER TABLE booking ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE booking DROP phone_number');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67D5399DD4E6F81 ON house (address)');
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
