<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;
use App\Models\AiConversationModel;
use App\Models\AiMesagesModel;

class Chat extends BaseController
{
    protected string $viewsFolder;
    protected AiConversationModel $aiConversationModel;
    protected AiMesagesModel $aiMesagesModel;

    public function __construct()
    {
        $this->viewsFolder = 'm/chat/';
        $this->aiMesagesModel = new AiMesagesModel();
        $this->aiConversationModel = new AiConversationModel();
    }

    public function index()
    {
        return redirect()->to('m/chat/conversation');
    }

    /**
     * Vista de conversacion chat.
     */
    public function conversation($conversationId)
    {
        $conversation = $this->aiConversationModel->find($conversationId);

        if ($conversation === null) {
            return redirect()->to('m/info/no_permitido');
        }

        $sitAgent = $this->aiConversationModel->sitAgent($conversation['agent_id']);
        $sitAgent = $sitAgent ?? (object) [
            'title' => 'Chat',
            'url_thumbnail' => URL_IMG . 'users/user.png',
        ];

        $data['headTitle'] = $sitAgent->title ?? 'Chat';
        $data['conversation'] = $conversation;
        $data['sitAgent'] = $sitAgent;
        $data['viewA'] = $this->viewsFolder . 'conversation/conversation';
        $data['messages'] = $this->aiMesagesModel->getMessages((int) $conversationId);

        return view(TPL_PUBLIC . 'main', $data);
    }
}