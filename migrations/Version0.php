<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version0 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE relationship (id BLOB NOT NULL --(DC2Type:ulid)
        , from_user BLOB NOT NULL --(DC2Type:ulid)
        , to_user BLOB NOT NULL --(DC2Type:ulid)
        , capability VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_200444A0F8050BAA FOREIGN KEY (from_user) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_200444A06A7DC786 FOREIGN KEY (to_user) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_200444A0F8050BAA ON relationship (from_user)');
        $this->addSql('CREATE INDEX IDX_200444A06A7DC786 ON relationship (to_user)');
        $this->addSql('CREATE UNIQUE INDEX link_unique_idx ON relationship (from_user, to_user)');
        $this->addSql('CREATE TABLE user (id BLOB NOT NULL --(DC2Type:ulid)
        , username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE TABLE weight_record (id BLOB NOT NULL --(DC2Type:ulid)
        , owner_id BLOB NOT NULL --(DC2Type:ulid)
        , date DATE NOT NULL --(DC2Type:date_immutable)
        , weight SMALLINT NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_506A8B487E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_506A8B487E3C61F9 ON weight_record (owner_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE relationship');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE weight_record');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
