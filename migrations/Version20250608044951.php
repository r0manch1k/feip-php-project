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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE booking_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE house_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE telegram_user_id_seq CASCADE');
        $this->addSql('CREATE TABLE bookings (
          id SERIAL NOT NULL,
          user_id INT DEFAULT NULL,
          telegram_bot_user_id INT DEFAULT NULL,
          house_id INT NOT NULL,
          comment VARCHAR(255) DEFAULT NULL,
          start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7A853C35A76ED395 ON bookings (user_id)');
        $this->addSql('CREATE INDEX IDX_7A853C35E81A3623 ON bookings (telegram_bot_user_id)');
        $this->addSql('CREATE INDEX IDX_7A853C356BB74515 ON bookings (house_id)');
        $this->addSql('CREATE TABLE houses (
          id SERIAL NOT NULL,
          address VARCHAR(255) NOT NULL,
          price INT NOT NULL,
          type VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_95D7F5CBD4E6F81 ON houses (address)');
        $this->addSql('CREATE TABLE summer_houses (
          id INT NOT NULL,
          bedrooms INT DEFAULT NULL,
          distance_from_sea INT DEFAULT NULL,
          has_shower BOOLEAN DEFAULT NULL,
          has_bathroom BOOLEAN DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE telegram_users (
          id SERIAL NOT NULL,
          telegram_id BIGINT NOT NULL,
          username VARCHAR(255) NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(255) DEFAULT NULL,
          phone_number VARCHAR(16) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_948A6ABCC0B3066 ON telegram_users (telegram_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_948A6ABF85E0677 ON telegram_users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_948A6AB6B01BC5B ON telegram_users (phone_number)');
        $this->addSql('ALTER TABLE
          bookings
        ADD
          CONSTRAINT FK_7A853C35A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          bookings
        ADD
          CONSTRAINT FK_7A853C35E81A3623 FOREIGN KEY (telegram_bot_user_id) REFERENCES telegram_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          bookings
        ADD
          CONSTRAINT FK_7A853C356BB74515 FOREIGN KEY (house_id) REFERENCES summer_houses (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          summer_houses
        ADD
          CONSTRAINT FK_2F36BE3BF396750 FOREIGN KEY (id) REFERENCES houses (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE summer_house DROP CONSTRAINT fk_91929996bf396750');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT fk_e00cedde6bb74515');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT fk_e00ceddea76ed395');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT fk_e00ceddee81a3623');
        $this->addSql('DROP TABLE house');
        $this->addSql('DROP TABLE summer_house');
        $this->addSql('DROP TABLE telegram_user');
        $this->addSql('DROP TABLE booking');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE booking_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE house_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE telegram_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE house (
          id SERIAL NOT NULL,
          address VARCHAR(255) NOT NULL,
          price INT NOT NULL,
          type VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_67d5399dd4e6f81 ON house (address)');
        $this->addSql('CREATE TABLE summer_house (
          id INT NOT NULL,
          bedrooms INT DEFAULT NULL,
          distance_from_sea INT DEFAULT NULL,
          has_shower BOOLEAN DEFAULT NULL,
          has_bathroom BOOLEAN DEFAULT NULL,
          PRIMARY KEY(id)
        )');
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
        $this->addSql('CREATE TABLE booking (
          id SERIAL NOT NULL,
          house_id INT NOT NULL,
          user_id INT DEFAULT NULL,
          telegram_bot_user_id INT DEFAULT NULL,
          comment VARCHAR(255) DEFAULT NULL,
          start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_e00ceddee81a3623 ON booking (telegram_bot_user_id)');
        $this->addSql('CREATE INDEX idx_e00ceddea76ed395 ON booking (user_id)');
        $this->addSql('CREATE INDEX idx_e00cedde6bb74515 ON booking (house_id)');
        $this->addSql('ALTER TABLE
          summer_house
        ADD
          CONSTRAINT fk_91929996bf396750 FOREIGN KEY (id) REFERENCES house (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT fk_e00cedde6bb74515 FOREIGN KEY (house_id) REFERENCES summer_house (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT fk_e00ceddea76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          booking
        ADD
          CONSTRAINT fk_e00ceddee81a3623 FOREIGN KEY (telegram_bot_user_id) REFERENCES telegram_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C35A76ED395');
        $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C35E81A3623');
        $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C356BB74515');
        $this->addSql('ALTER TABLE summer_houses DROP CONSTRAINT FK_2F36BE3BF396750');
        $this->addSql('DROP TABLE bookings');
        $this->addSql('DROP TABLE houses');
        $this->addSql('DROP TABLE summer_houses');
        $this->addSql('DROP TABLE telegram_users');
    }
}
