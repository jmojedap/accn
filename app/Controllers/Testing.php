<?php

namespace App\Controllers;

class Testing extends BaseController
{
    public function index()
    {
        $request = \Config\Services::request();

        $data['get'] = $request->getCookie('lepetit_session');

        return $this->response->setJSON($data);
    }

    public function dbToolsRowId()
    {
        $data['userNull'] = $this->dbTools->rowId('users',null);
        $data['userSi'] = $this->dbTools->rowId('users',1982);
        $data['userNoExiste'] = $this->dbTools->rowId('users',1982564654);

        return $this->response->setJSON($data);
    }

    /**
     * Probando función dbTools->row
     * 2023-01-29
     */
    public function dbToolsRow()
    {
        $data['userNull'] = $this->dbTools->row('users',null);
        $data['userSi'] = $this->dbTools->row('users','id = 1982');
        $data['userSiSelect'] = $this->dbTools->row('users','id = 1982', 'id, username');
        $data['userNoExiste'] = $this->dbTools->row('users','id = 1654987');

        return $this->response->setJSON($data);
    }
}
