<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;
use App\Models\AiGenerateModel;
use App\Models\AiConversationModel;
use App\Models\AiMesagesModel;

class Chat extends BaseController
{
    public function __construct()
	{
        $this->viewsFolder = 'm/chat/';
		$this->aiMesagesModel = new AiMesagesModel();
        $this->aiConversationModel = new AiConversationModel();
		/*$this->db = \Config\Database::connect();
		$this->userModel = new UserModel();
        $this->viewsFolder = 'admin/users/';
        $this->backLink = URL_ADMIN . 'users/explore';
        $this->entityInfo = [
            'controller' => 'users',
            'singular' => 'Usuario',
            'plural' => 'Usuarios',
            'isMale' => 1,
        ];*/
	}

    public function index()
    {
        return redirect()->to('m/chat/conversation');
    }

    /**
     * Vista de conversación chat
     * 2026-03-04
     */
    public function conversation($conversationId)
    {
        $conversation = $this->aiConversationModel->find($conversationId);
        $sitAgent = $this->aiConversationModel->sitAgent($conversation['agent_id']);

        $data['headTitle'] = 'Chat';
        $data['conversation'] = $conversation;
        $data['sitAgent'] = $sitAgent;
        $data['viewA'] = $this->viewsFolder . 'conversation/conversation';
        $data['messages'] = $this->aiMesagesModel->getMessages($conversationId);  
        return view(TPL_PUBLIC . 'main', $data);
    }
}