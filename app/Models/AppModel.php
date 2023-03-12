<?php namespace App\Models;

use CodeIgniter\Model;

class AppModel extends Model
{

    public function view($data)
    {
        $view = \Config\Services::renderer();

        $result['headTitle'] = '';
        $result['viewA'] = '';

        if ( isset($data['viewA']) ) { $result['viewA'] = $view->render($data['viewA'], $data); }

        /*$data['headTitle'] = 'Probando render';
        $data['viewA'] = $view->render('formulario');*/
        return $this->response->setJSON($result);
        
        //return $result;
    }
}