<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;
use App\Models\AiGenerateModel;
use App\Models\AiMesagesModel;

class Chat extends BaseController
{
    public function __construct()
	{
        $this->viewsFolder = 'm/chat/';
		$this->aiMesagesModel = new AiMesagesModel();
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

    public function conversation()
    {
        $data['headTitle'] = 'Chat';
        $data['viewA'] = $this->viewsFolder . 'conversation/conversation';
        $data['messages'] = $this->aiMesagesModel->getMessages(1);  
        return view(TPL_PUBLIC . 'main', $data);
    }
}