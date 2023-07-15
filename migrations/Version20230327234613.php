<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327234613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sale CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE external_sales_order_number external_sales_order_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sale_line_items CHANGE sale_id sale_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sale_line_items CHANGE sale_id sale_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sale CHANGE id id INT NOT NULL, CHANGE external_sales_order_number external_sales_order_number INT NOT NULL');
    }
}
