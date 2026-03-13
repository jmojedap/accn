<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\GeminiClient;
use App\Models\AiConversationModel;

class AiConversations extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->aiConversationModel = new AiConversationModel();
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

        $aRow['created_at'] = date('Y-m-d H:i:s');
        $aRow['updated_at'] = date('Y-m-d H:i:s');
        $aRow['user_1_id'] = $_SESSION['user_id'];
        $aRow['title'] = 'Nueva conversación';

        $data['conversation_id'] = $this->aiConversationModel->insert($aRow);
        return $this->response->setJSON($data);
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