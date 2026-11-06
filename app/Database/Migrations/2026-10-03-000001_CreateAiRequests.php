<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateAiRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'tool' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => false,
            ],
            'tool_type_request' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => false,
            ],
            'proveedor' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'input_tokens' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'output_tokens' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'request_json' => [
                'type' => 'JSON',
                'null' => false,
            ],
            'response_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'response_content' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'response_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Valores sugeridos: plain_text, markdown, html, other',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'pending',
                'comment' => 'Valores sugeridos: pending, success, error',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'related_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'related_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'creator_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at', false, false, 'idx_ai_requests_created_at');
        $this->forge->addKey(['proveedor', 'modelo'], false, false, 'idx_ai_requests_provider_model');
        $this->forge->addKey('creator_id', false, false, 'idx_ai_requests_creator_id');
        $this->forge->addKey(['related_type', 'related_id'], false, false, 'idx_ai_requests_related');

        $this->forge->createTable('ai_requests', false, [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('ai_requests', true);
    }
}
