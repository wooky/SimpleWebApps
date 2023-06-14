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
        $this->addSql('CREATE TABLE artefact (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', image_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_public TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CBE5A3313DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_ownership (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', owner_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', book_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', state VARCHAR(255) NOT NULL, INDEX IDX_A3EFAD27E3C61F9 (owner_id), INDEX IDX_A3EFAD216A2B381 (book_id), UNIQUE INDEX UNIQ_A3EFAD27E3C61F916A2B381 (owner_id, book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE relationship (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', from_user BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', to_user BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', capability VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_200444A0F8050BAA (from_user), INDEX IDX_200444A06A7DC786 (to_user), UNIQUE INDEX link_unique_idx (from_user, to_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, google_authenticator_secret VARCHAR(64) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE weight_record (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', owner_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', weight SMALLINT NOT NULL, INDEX IDX_506A8B487E3C61F9 (owner_id), UNIQUE INDEX weight_record_date_unique_idx (owner_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A3313DA5256D FOREIGN KEY (image_id) REFERENCES artefact (id)');
        $this->addSql('ALTER TABLE book_ownership ADD CONSTRAINT FK_A3EFAD27E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE book_ownership ADD CONSTRAINT FK_A3EFAD216A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE relationship ADD CONSTRAINT FK_200444A0F8050BAA FOREIGN KEY (from_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE relationship ADD CONSTRAINT FK_200444A06A7DC786 FOREIGN KEY (to_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE weight_record ADD CONSTRAINT FK_506A8B487E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A3313DA5256D');
        $this->addSql('ALTER TABLE book_ownership DROP FOREIGN KEY FK_A3EFAD27E3C61F9');
        $this->addSql('ALTER TABLE book_ownership DROP FOREIGN KEY FK_A3EFAD216A2B381');
        $this->addSql('ALTER TABLE relationship DROP FOREIGN KEY FK_200444A0F8050BAA');
        $this->addSql('ALTER TABLE relationship DROP FOREIGN KEY FK_200444A06A7DC786');
        $this->addSql('ALTER TABLE weight_record DROP FOREIGN KEY FK_506A8B487E3C61F9');
        $this->addSql('DROP TABLE artefact');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE book_ownership');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE relationship');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE weight_record');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
