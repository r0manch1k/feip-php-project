<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526000041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (
          id SERIAL NOT NULL,
          user_id INT NOT NULL,
          house_id INT NOT NULL,
          comment VARCHAR(255) DEFAULT NULL,
          start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDE6BB74515 ON booking (house_id)');
        $this->addSql('CREATE TABLE house (
          id SERIAL NOT NULL,
          address VARCHAR(255) NOT NULL,
          price INT NOT NULL,
          type VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67D5399DD4E6F81 ON house (address)');
        $this->addSql('CREATE TABLE refresh_tokens (
          id SERIAL NOT NULL,
          refresh_token VARCHAR(128) NOT NULL,
          username VARCHAR(255) NOT NULL,
          valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE summer_house (
          id INT NOT NULL,
          bedrooms INT DEFAULT NULL,
          distance_from_sea INT DEFAULT NULL,
          has_shower BOOLEAN DEFAULT NULL,
          has_bathroom BOOLEAN DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE "user" (
          id SERIAL NOT NULL,
          phone_number VARCHAR(16) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496B01BC5B ON "user" (phone_number)');
        $this->addSql('CREATE TABLE messenger_messages (
          id BIGSERIAL NOT NULL,
          body TEXT NOT NULL,
          headers TEXT NOT NULL,
          queue_name VARCHAR(190) NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE
        OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$ BEGIN
          PERFORM pg_notify(
            \'messenger_messages\', NEW.queue_name :: text
          );

          RETURN NEW;
        END;

        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT
        OR
        UPDATE
          ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT FK_E00CEDDE6BB74515 FOREIGN KEY (house_id) REFERENCES summer_house (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          summer_house
        ADD
          CONSTRAINT FK_91929996BF396750 FOREIGN KEY (id) REFERENCES house (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDE6BB74515');
        $this->addSql('ALTER TABLE summer_house DROP CONSTRAINT FK_91929996BF396750');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE house');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE summer_house');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
