<?php

namespace App\Controllers;

use App\Controllers\BaseController; // Which BaseController are you referring to.

class Info extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function test()
    {
        $data['userNull'] = $this->dbTools->rowId('users',null);
        $data['userSi'] = $this->dbTools->rowId('users',1982);
        $data['userNoExiste'] = $this->dbTools->rowId('users',1982564654);

        return $this->response->setJSON($data);
    }

    public function noPermitido(){
        echo 'Acceso no permitido';
    }
}
