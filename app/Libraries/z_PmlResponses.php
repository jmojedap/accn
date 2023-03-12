<?php

namespace App\Libraries;

class PmlResponses
{
    public function view($viewName, $data)
    {
        $request = \Config\Services::request();
        
        if ( $request->getGet('json') ) {
            // Procesar los datos recibidos en $input
            $result['headTitle'] = '';
            $result['viewA'] = '';

            $view = \Config\Services::renderer();
            if ( isset($data['headTitle']) ) { $result['headTitle'] = $data['headTitle']; }
            if ( isset($data['viewA']) ) { $result['viewA'] = $view->render($data['viewA'], $data); }

            // Crear la respuesta en formato JSON
            $response = service('response');
            $response->setContentType('application/json')
                ->setBody(json_encode($result))
                ->send();
        } else {
            // Crear la respuesta vista normal en formato HTML
            return view($viewName, $data);
        }
    }
}
