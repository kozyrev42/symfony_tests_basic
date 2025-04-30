<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250430023847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц user, post и comment с внешними ключами, индексами и автоинкрементными ID';
    }

    public function up(Schema $schema): void
    {
        // Последовательности для автоинкрементных ID
        $this->addSql('CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE post_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('
            CREATE TABLE "user" (
                id INT NOT NULL DEFAULT nextval(\'user_id_seq\'),
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE TABLE post (
                id INT NOT NULL DEFAULT nextval(\'post_id_seq\'),
                author_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

         // Создание индекса для ускорения выборки постов по author_id (внешний ключ на таблицу user)
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');

        $this->addSql('
            CREATE TABLE comment (
                id INT NOT NULL DEFAULT nextval(\'comment_id_seq\'),
                post_id INT NOT NULL,
                text TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)');

        // Добавление внешнего ключа: post.author_id → user.id (автор поста)
        $this->addSql('
            ALTER TABLE post 
            ADD CONSTRAINT FK_5A8A6C8DF675F31B 
            FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        // Добавление внешнего ключа: comment.post_id → post.id (комментарий принадлежит посту)
        $this->addSql('
            ALTER TABLE comment 
            ADD CONSTRAINT FK_9474526C4B89032C 
            FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE post_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');

        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DF675F31B');

        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE "user"');
    }
}
