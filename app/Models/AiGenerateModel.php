<?php namespace App\Models;

use CodeIgniter\Model;

class AiGenerateModel extends Model
{

    public function getAnswer($prompt)
    {
        $geminiClient = new GeminiClient();
        $contents = [
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => $prompt,
                    ],
                ],
            ],
        ];
        $response = $geminiClient->generate($contents);
        return $response;
    }

    /**
     * Obtiene los mensajes de una conversación como contenido para el chat
     * 
     * @param int $conversationId
     * @return array
     */
    public function getMessagesAsContent($conversationId): array
    {

        $messages = $this->db->table('ai_messages')->where('conversation_id', $conversationId)->get()->getResult();
        $contents = [];
        foreach ($messages as $message) {
            $contents[] = [
                'role' => $message->role,
                "parts" => [
                    [
                        "text" => $message->text,
                    ],
                ],
            ];
        }
        return $contents;
    }
}