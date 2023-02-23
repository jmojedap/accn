<?php
namespace App\Libraries;

class Pml {

    /** ACTUALIZADA 2020-09-03 */
    
    /**
     * Converts codeigniter query object in an array
     * 
     * $index_field: field name to be the index (key) of returned array
     * $value_field: field to be the values in the returned array
     */
    function query_to_array($query, $value_field, $index_field = NULL)
    {
        $array = array();   
        foreach ($query->getResult() as $row)
        {
            if ( is_null($index_field) ) {
                //Sin índice
                $array[] = $row->$value_field;
            } else {
                $index = $row->$index_field;
                $array[$index] = $row->$value_field;
            }
        }
        
        return $array;
    }

    /**
     * Convierte el conjunto de valores de un $field de un $query en un string
     * separado ($separator) por un caracter
     * 
     * @param type $query
     * @param type $field
     * @param type $separator
     * @return type
     */
    function query_to_str($query, $field, $separator = ',')
    {
        $str = '';
        
        foreach ($query->result() as $row)
        {
            $str .= $row->$field . $separator;
        }
        
        //Se quita el separador final con substr
        return substr($str, 0, -strlen($separator));
    }

    /**
     * Array que puede ser usado como el listado de opciones de un input select html
     * 2020-10-27
     */
    function query_to_options($query, $value_field, $index_field = 'id', $empty_value = '')
    {
        $array = array();

        if ( strlen($empty_value) ) $array[''] = '>> ' . $empty_value . ' <<';

        foreach ($query->getResult() as $row)
        {
            $index = '0' . $row->$index_field;
            $array[$index] = $row->$value_field;
        }
        
        return $array;
    }
    
// CONTROL FUNCTIONS
//-----------------------------------------------------------------------------
    
    /**
     * Returns 1 or another value dependiendo de si una variable es equal a zero o no
     * Si es zero devuelve $value_if_zero
     * Si no es vacío devuelve $value_no_zero
     * Función utilizada para evitar errores provocados al utilizar una función 
     * con value vacío principalmente para comprobar si un campo de una tabla 
     * en la base de datos tiene un value
     * 
     * @param type $variable
     * @param type $value_if_zero
     * @param type $value_no_zero
     * @return type 
     */
    function if_zero($variable, $value_if_zero, $value_no_zero = NULL)
    {   
        if ( is_null($value_no_zero) ) { $value_no_zero = $variable; }
        if ( $variable == 0 ) {
            $if_zero = $value_if_zero;
        } else {
            $if_zero = $value_no_zero;
        }
        
        return $if_zero;
    }

    /**
     * Si la longitud de una cadena es cero, devuelve un value_si
     * Si la longitud no es cero, devuelve un $value_no
     * Si el $value_no es null, devuelve el value de la variable
     * 
     * @param type $variable
     * @param type $value_if
     * @param type $value_else
     * @return type
     */
    function if_strlen($variable, $value_if, $value_else = NULL)
    {
        if ( is_null($value_else) ) { $value_else = $variable; }
        
        if ( strlen($variable) == 0 ) 
        {
            $if_strlen = $value_if;
        } else {
            $if_strlen = $value_else;
        }
        return $if_strlen;
    }

    /**
     * Alterna una variable entre dos valores, intercambiando el valor actual
     * por el otro.
     * 
     * @param type $current_value
     * @param type $value_1
     * @param type $value_2
     * @return type
     */
    function toggle($current_value, $value_1 = 0, $value_2 = 0)
    {
        $new_value = $value_2;
        if ( $current_value == $value_2 ) { $new_value = $value_1; }
        
        return $new_value;
    }

    //Devuelve cantidad en formato de dinero
    function money($amount, $format = 'S0', $factor = 1)
    {
        $number = $amount / $factor;

        $money = $amount;
        if ( $format == 'S0' ){
            $money = '$ ' . number_format($number, 0, ',', '.');
        }

        return $money;
    }

    /**
     * Devuelve el valor de una clase html dependiendo de si un valor es igual
     * a un elemento actual con el que se compara. Si son iguales se devuelve 
     * la clase $active (Para resaltarlo como actual). Si son diferentes se
     * devuelve la clase $inactive
     * 2020-09-03
     */
    function active_class($current_element, $compare_element, $active, $inactive = '')
    {
        $active_class = $inactive;
        if ( $current_element == $compare_element ) { $active_class = $active; }
        
        return $active_class;
    }

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
     * Entrada en el format YYYY-MM-DD hh:mm:ss
     * Devuelve una cadena con el format especificado de date
     * 
     * @param type $date
     * @param type $format
     * @return string
     */
    function date_format($date, $format = 'Y-M-d H:i')
    {
        $obj_date = new DateTime($date);
        $date_format = $obj_date->format($format);
        return $date_format;
    }

    /**
     * 
     * Cantidad de tiempo que pasan entre dos fechas
     * 2018-12-17
     * 
     * @param type $start
     * @param type $end
     * @return type
     */
    function interval($start, $end)
    {
        $interval = 'ND';
        if ( ! is_null($start) ) 
        {
            $arr_seconds = $this->arr_seconds();

            //Marcas de tiempo, se calcula diferencia ($seconds)
            $mkt1 = strtotime(substr($start . ' 00:00:00', 0, 19));
            $mkt2 = strtotime(substr($end . ' 00:00:00', 0, 19));
            $seconds = abs($mkt2 - $mkt1);

            if ( $seconds < $arr_seconds['minute'] )
            {
                $time_units = 1;
                $sufix = " min";
            } elseif ( $seconds < $arr_seconds['hour'] ){
                $time_units = $seconds / $arr_seconds['minute'];
                $sufix = ' min';
            } elseif ( $seconds < $arr_seconds['day'] ){
                $time_units = $seconds / $arr_seconds['hour'];
                $sufix = ' h';
            } elseif ( $seconds < $arr_seconds['week'] ){
                $time_units = $seconds / $arr_seconds['day'];
                $sufix = ' d';
            } elseif ($seconds < $arr_seconds['month']){
                $time_units = $seconds / $arr_seconds['week'];
                $sufix =' sem';
            } elseif ($seconds < $arr_seconds['year']){
                $time_units = $seconds / $arr_seconds['month'];
                $sufix = ' meses';
            } else {
                $time_units = $seconds / $arr_seconds['year'];
                $sufix = ' a&ntildeos';
            }
            
            //Se agrega la unidad de medida
            $interval = round($time_units, 1, PHP_ROUND_HALF_DOWN) . $sufix;
        }

        return $interval;
    }

    //Array con el número de segundos que tiene cada periodo
    function arr_seconds()
    {
        $arr_seconds = array(
            'year' => 31557600,
            'month' => 2629800,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
        );
        
        return $arr_seconds;
    }

    /**
     * String con la cantidad de tiempo que ha pasado desde hace una fecha determinada
     * la variable $start debe tener el formato YYYY-MM-DD hh:mm:ss, utilizado en MySQL 
     * para los campos de fecha
     * 
     * @param type $start
     * @return type 
     */
    function ago($start, $with_prefix = TRUE)
    {
        $prefix = 'Hace ';
        if ( $start > date('Y-m-d H:i:s') ) { $prefix = 'Dentro de '; }

        $ago = $this->interval($start, date('Y-m-d H:i:s'));
        if ( $with_prefix ) { $ago = $prefix . $ago; }

        return $ago;
    }

    /**
     * Años que han pasado desde una fecha
     */
    function age($date)
    {
        $age = '-';
        if( ! is_null($date) )
        {
            $mkt = strtotime(substr($date . ' 00:00:00', 0, 19));
            $age = ceil((time()-$mkt)/(60*60*24*365.25)) - 1;
        }
        return $age;
    }

    /**
     * Cantidad de segundos entre dos momentos
     */
    function seconds($start, $end)
    {
        $mkt1 = strtotime(substr($start . ' 00:00:00', 0, 19));
        $mkt2 = strtotime(substr($end . ' 00:00:00', 0, 19));
        $seconds = abs($mkt2 - $mkt1);

        return $seconds;
    }

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
    function get_url_content($url)
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