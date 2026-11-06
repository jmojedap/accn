<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AiConversationModel;
use App\Models\AiMesagesModel;
use App\Models\AiRequestModel;

class AiConversations extends BaseController
{
    protected $db;
    protected AiConversationModel $aiConversationModel;
    protected AiMesagesModel $aiMesagesModel;
    protected AiRequestModel $aiRequestModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->aiConversationModel = new AiConversationModel();
        $this->aiMesagesModel = new AiMesagesModel();
        $this->aiRequestModel = new AiRequestModel($this->db);
    }
// Funciones
//-----------------------------------------------------------------------------

    /**
     * POST :: Crea una nueva conversación
     * 2026-03-04
     */
    public function create()
    {
        $payload = $this->request->getJSON(true);
        $aRow = $payload['data'];
        
        // Verificar si existe ya una conversación con el mismo agent_id, user_id y tipo (ai-chat)
        $conversation = $this->aiConversationModel
            ->where('agent_id', $aRow['agent_id'])
            ->where('user_1_id', $_SESSION['user_id'])
            ->where('type', 'ai-chat')
            ->first();
        if ($conversation) {
            $data['conversation_id'] = $conversation['id'];
            return $this->response->setJSON($data);
        }

        //Si no existe crear
        $sitAgent = $this->aiConversationModel->sitAgent($aRow['agent_id']);

        $aRow['system_instruction_key'] = $sitAgent->system_instruction_key;
        $aRow['created_at'] = date('Y-m-d H:i:s');
        $aRow['updated_at'] = date('Y-m-d H:i:s');
        $aRow['user_1_id'] = $_SESSION['user_id'];
        $aRow['title'] = 'Nueva conversación';

        $data['conversation_id'] = $this->aiConversationModel->insert($aRow);
        return $this->response->setJSON($data);
    }

    /**
     * GET :: Obtiene la información de una conversación
     * 2026-03-04
     */
    public function conversationInfo($id)
    {
        $conversation = $this->aiConversationModel->find($id);
        return $this->response->setJSON($conversation);
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

        // Guardar el mensaje del usuario y generar la respuesta IA
        $userMessageId = $this->aiMesagesModel->insertUserMessage([
            'conversation_id' => $conversation['id'],
            'text' => $prompt,
        ]);

        // Generar la respuesta IA y persistirla
        $data = $this->aiConversationModel->getConversationAnswer(
            $conversation,
            $this->aiRequestModel->requestOptions($inputData)
        );

        $data['user_message_id'] = $userMessageId;

        if (($data['error'] ?? '') === '') {
            $data['ai_message_id'] = $this->aiMesagesModel->insertAiMessage(
                $conversation['id'],
                $data['response_text'],
                $data['ai_request_id']
            );

            $this->aiConversationModel->summarizeConversation($conversation['id']);
        }

        return $this->response->setJSON($data);
    }

    /**
     * GET :: Obtiene los mensajes mas recientes con limit y offset en la consulta.
     * 2026-10-03
     */
    public function getMessages($conversationId)
    {
        $conversationId = filter_var($conversationId, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $limit = filter_var($this->request->getGet('limit') ?? 50, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $offset = filter_var($this->request->getGet('offset') ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        if ($conversationId === false || $limit === false || $offset === false) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'invalid_pagination',
                    'message' => 'conversationId y limit deben ser enteros positivos; offset debe ser un entero no negativo.',
                ]);
        }

        if ($this->aiConversationModel->find($conversationId) === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'conversation_not_found',
                    'message' => 'Conversacion no encontrada.',
                ]);
        }

        $messages = $this->aiMesagesModel->getRecentMessages($conversationId, $limit, $offset);

        return $this->response->setJSON([
            'conversation_id' => $conversationId,
            'limit' => $limit,
            'offset' => $offset,
            'messages' => $messages,
        ]);
    }

    /**
     * DELETE :: Elimina una conversación
     * 2026-03-04
     */
    public function deleteConversation($id)
    {
        $conversation = $this->aiConversationModel->delete($id);
        return $this->response->setJSON($conversation);
    }
}
