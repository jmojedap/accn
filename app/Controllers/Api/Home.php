<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index()
    {
        //return view('Bienvenido a la API');
    }

    public function test()
    {
        $data['message'] = 'Bienvenido a la API ACC';

        return $this->response->setJSON($data);
    }
}
