<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\GeminiClient;
use App\Models\AiGenerateModel;

class AiConversationModel extends Model
{
    protected $table = 'ai_conversations';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'name',
        'description',
        'user_1_id',
        'user_2_id',
        'agent_id',
        'type',
        'system_instruction_key',
        'related_id',
        'summary',
        'prompt_token_count',
        'candidates_token_count',
        'token_count',
        'updater_id',
        'updated_at',
        'creator_id',
        'created_at'
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function sitAgent($sitId)
    {
        //Devolver datos del sit, tabla posts
        $sit = $this->db->table('posts')
            ->select('id, title, url_image, url_thumbnail, text_1 as system_instruction_key')
            ->where('id', $sitId)
            ->get()
            ->getRow();

        return $sit;
    }

    /**
     * Obtiene los mensajes de una conversacion como contenido para Gemini.
     * Tabla ai_messages.
     */
    public function getMessagesAsContent(int $conversationId, int $limit = 25): array
    {
        $messages = $this->db->table('ai_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();

        // Se seleccionan los mensajes mas recientes y se entregan en orden cronologico.
        $messages = array_reverse($messages);

        $contents = [];

        foreach ($messages as $message) {
            if (trim((string) $message->text) === '') {
                continue;
            }

            $contents[] = $this->messageAsContent($message->role, $message->text);
        }

        return $contents;
    }

    /**
     * Convierte un mensaje de la conversacion a la estructura contents de Gemini.
     */
    protected function messageAsContent(string $role, string $text): array
    {
        return [
            'role' => $role === 'model' ? 'model' : 'user',
            'parts' => [
                [
                    'text' => trim($text),
                ],
            ],
        ];
    }

    /**
     * Genera y guarda un resumen objetivo del historial de la conversacion.
     *
     * @param int $conversationId
     * @return array
     */
    public function summarizeConversation(int $conversationId): array
    {
        // Validar que la conversacion exista antes de llamar a la API de IA.
        if ($this->find($conversationId) === null) {
            return [
                'conversation_id' => $conversationId,
                'summary' => null,
                'error' => 'La conversacion no existe.',
            ];
        }

        // Convertir el historial al formato contents que espera Gemini.
        $contents = $this->getMessagesAsContent($conversationId);
        // Agregar un mensaje para evitar "Requests ending with a model turn are not supported."
        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => 'Resume la conversación.',
                ],
            ],
        ];

        // No se puede generar un resumen si no hay mensajes con texto.
        if ($contents === []) {
            return [
                'conversation_id' => $conversationId,
                'summary' => null,
                'error' => 'La conversacion no contiene mensajes para resumir.',
            ];
        }

        $geminiClient = new GeminiClient();

        $generationConfig = $geminiClient->defaultGenerationSettings();
        $generationConfig['max_output_tokens'] = 3000; // Limitar el resumen a 3000 tokens

        // Esta instruccion es especifica para resumir. No se usa la personalidad
        // de Paula porque esa instruccion esta pensada para responder al usuario.
        $requestOptions = [
            'contents' => $contents,
            'system_instruction_parts' => [[
                'text' => 'Resume la conversacion de forma objetiva. '
                    . 'Incluye el tema principal, información sobre el interlocutor (user) las necesidades o decisiones importantes'
                    . 'Incluye lo más relevante mencionado por el user, y por model.'
                    . 'y los asuntos pendientes. Devuelve unicamente el resumen en espanol, '
                    . 'sin saludar ni responder como uno de los participantes.',
            ]],
            'generation_config' => $generationConfig,
        ];

        $aiGenerateModel = new AiGenerateModel();

        // AiGenerateModel devuelve response_text, response_details y error;
        // no devuelve candidates en el nivel superior.
        $data = $aiGenerateModel->generate($requestOptions);
        log_message('debug', 'Resumen de la conversacion: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        // No sobrescribir un resumen valido con un mensaje generico si Gemini fallo.
        if (($data['error'] ?? '') !== '') {
            log_message('error', 'No fue posible resumir la conversacion: ' . $data['error']);

            return [
                'conversation_id' => $conversationId,
                'summary' => null,
                'error' => $data['error'],
            ];
        }

        // GeminiClient ya extrajo el texto desde candidates y lo normalizo.
        $summary = trim((string) ($data['response_text'] ?? ''));

        if ($summary === '') {
            return [
                'conversation_id' => $conversationId,
                'summary' => null,
                'error' => 'Gemini no devolvio un resumen valido.',
            ];
        }

        $aRow = [
            'summary' => $summary,
            'updater_id' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Guardar unicamente el resumen valido en ai_conversations.summary.
        if (! $this->update($conversationId, $aRow)) {
            $error = 'No fue posible guardar el resumen de la conversacion.';
            log_message('error', $error . ' ' . json_encode($this->errors(), JSON_UNESCAPED_UNICODE));

            return [
                'conversation_id' => $conversationId,
                'summary' => null,
                'error' => $error,
            ];
        }

        return [
            'conversation_id' => $conversationId,
            'summary' => $summary,
            'error' => '',
        ];
    }
}
