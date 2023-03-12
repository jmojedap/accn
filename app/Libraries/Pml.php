<?php
namespace App\Libraries;

class Pml {

/** ACTUALIZADA 2023-03-04 */
    
// ROUTING
//-----------------------------------------------------------------------------

    public function view($viewName, $data)
    {
        $request = \Config\Services::request();
        
        if ( $request->getGet('json') ) {
            // Procesar los datos recibidos en $input
            $result['headTitle'] = '';
            if ( isset($data['headTitle']) ) { $result['headTitle'] = $data['headTitle']; }

            //Partes de la página html
            $parts = ['viewA','nav2','nav3'];

            $view = \Config\Services::renderer();
            foreach ($parts as $part) {
                $result[$part] = '';    //Valor por defecto al no estar definido
                //Si se definió vista para esta parte de la página, renderizar
                if ( isset($data[$part]) ) { 
                    $result[$part] = $view->setData($data)->render($data[$part]);
                }
            }

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
    
// CONTROL FUNCTIONS
//-----------------------------------------------------------------------------
    

    /**
     * Devuelve password encriptado
     * 2022-07-02
     */
    function cryptPassword($input, $rounds = 7):string
    {
        $salt = '';
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++)
        {
          $salt .= $salt_chars[array_rand($salt_chars)];
        }
        
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }

// DATE FUNCTIONS
//-----------------------------------------------------------------------------

    /**
     * Convierte una fecha de excel en mktime de Unix
     * @param type $date_excel
     * @return type
     */
    function dexcel_unix($date_excel)
    {
        $hours_diff = 19; //Diferencia GMT
        return (( $date_excel - 25568 ) * 86400) - ($hours_diff * 60 * 60);
    }

    /**
     * Devuelve un valor entero de porcentaje (ya multiplicado por 100)
     * 2019-06-04
     * 
     * @param type $dividend
     * @param type $divider
     * @return int
     */
    function percent($dividend, $divider = 1, $decimals = 0)
    {
        $percent = 0;
        if ( $divider != 0 ) {
            $percent = number_format(100 * $dividend / $divider, $decimals);
        }
        return $percent;
    }

// URL
//-----------------------------------------------------------------------------

    /**
     * String, del contenido obtenido al ejecutar una URL
     * 2020-08-14
     */
    function getUrlContent($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => "",     // handle compressed
            CURLOPT_USERAGENT      => "test", // name of client
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        ); 

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}