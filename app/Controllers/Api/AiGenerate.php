<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AiConversationModel;
use App\Models\AiGenerateModel;
use App\Models\AiMesagesModel;

class AiGenerate extends BaseController
{
    protected $db;
    protected AiGenerateModel $aiGenerateModel;
    protected AiConversationModel $aiConversationModel;
    protected AiMesagesModel $aiMesagesModel;

    /**
     * Inicializa la conexion a base de datos y los modelos usados por el endpoint de chat IA.
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->aiGenerateModel = new AiGenerateModel();
        $this->aiConversationModel = new AiConversationModel();
        $this->aiMesagesModel = new AiMesagesModel();
    }

    /**
     * POST :: Valida la solicitud, guarda el mensaje del usuario, genera la respuesta IA y la persiste.
     */
    public function getAnswer()
    {
        $inputData = $this->request->getJSON(true) ?? [];
        $prompt = trim((string) ($inputData['prompt'] ?? ''));
        $conversationId = (int) ($inputData['conversation_id'] ?? 0);

        // El prompt no puede estar vacio y la conversacion debe ser valida
        if ($prompt === '' || $conversationId <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'Prompt o conversacion invalida.',
                    'response_text' => 'No fue posible procesar la solicitud.',
                ]);
        }

        // Verificar que la conversacion exista
        $conversation = $this->aiConversationModel->find($conversationId);

        // Si no existe la conversacion, devolver un error 404
        if ($conversation === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Conversacion no encontrada.',
                    'response_text' => 'No se encontro la conversacion solicitada.',
                ]);
        }

        $inputData['system_instruction_key'] = $conversation->system_instruction_key ?? 'ayudante';

        // Guardar el mensaje del usuario y generar la respuesta IA
        $userMessageId = $this->aiMesagesModel->insertUserMessage([
            'conversation_id' => $conversation['id'],
            'text' => $prompt,
        ]);

        // Generar la respuesta IA y persistirla
        $data = $this->aiGenerateModel->getConversationAnswer(
            $conversation,
            $this->aiGenerateModel->requestOptions($inputData)
        );

        $data['user_message_id'] = $userMessageId;

        if (($data['error'] ?? '') === '') {
            $data['ai_message_id'] = $this->aiMesagesModel->insertAiMessage(
                $conversation['id'],
                $data['response_text'],
                $data['response_details']
            );

            $this->aiConversationModel->summarizeConversation($conversation['id']);
        }

        return $this->response->setJSON($data);
    }

    /**
     * DELETE :: Valida la seleccion recibida y elimina mensajes de una conversacion.
     */
    public function deleteMessages()
    {
        $inputData = $this->request->getJSON(true) ?? [];
        $selected = $inputData['selected'] ?? [];
        $conversationId = (int) ($inputData['conversation_id'] ?? 0);

        if ($conversationId <= 0 || ! is_array($selected) || $selected === []) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['qty_deleted' => 0]);
        }

        $qtyDeleted = $this->aiMesagesModel->deleteMessages($selected, $conversationId);

        return $this->response->setJSON(['qty_deleted' => $qtyDeleted]);
    }

    /**
     * Extrae del payload solo las opciones permitidas para construir la solicitud a Gemini.
     */
    protected function z_requestOptions(array $inputData): array
    {
        $allowedKeys = [
            'model_id',
            'generate_content_format',
            'generation_config',
            'system_instruction_folder',
            'system_instruction_key',
        ];

        $requestOptions = [];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $inputData)) {
                $requestOptions[$key] = $inputData[$key];
            }
        }

        return $requestOptions;
    }
}
