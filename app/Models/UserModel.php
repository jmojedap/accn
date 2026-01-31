<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'idcode', 'password',
        'first_name','last_name','display_name','role','email','username',
        'document_number', 'document_type', 'gender', 'birth_date',
        'image_id', 'url_image', 'url_thumbnail',
        'creator_id', 'updater_id'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules    = [
        'email' => 'is_unique[users.email,id,{id}]|valid_email',
        'document_number' => 'is_unique[users.document_number,id,{id}]',
        'username' => 'is_unique[users.username,id,{id}]'
    ];
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'El correo electrónico ya está registrado',
            'valid_email' => 'Debe usar una dirección de correo elecrónico válida'
        ],
        'document_number' => [
            'is_unique' => 'El número de documento escrito ya está registrado'
        ],
        'username' => [
            'is_unique' => 'El username escrito ya está registrado'
        ]
    ];
    protected $skipValidation     = false;


// Buscador
//-----------------------------------------------------------------------------

    /**
     * Segmento SQL SELECT para construir consulta
     * 2020-08-11
     */
    function select($format = 'default')
    {
        $arrSelect['default'] = 'id, idcode, display_name, first_name, last_name, username, email, document_number,
            role, status, gender, birth_date, organization_id, city_id, phone_number,
            notes, url_image, url_thumbnail, image_id,
            updater_id, creator_id, updated_at, created_at';
        $arrSelect['basic'] = 'id, idcode, display_name, 
            username, email, role';
        $arrSelect['admin'] = '*';

        return $arrSelect[$format];
    }

    /**
     * Devuelve listado de usuarios filtrados o segmentaos por criterios en $input
     * @param array $input
     * @return array $data
     * 2024-05-23
     */
    public function search($input)
    {
        $qFields = ['display_name', 'first_name', 'last_name', 'email'];
        $filtersNames = ['q','role__eq','document_number__eq', 'gender__eq'];
        
        $search = new \App\Libraries\Search();
        $filters = $search->filters($input, $filtersNames);
        $settings = $search->settings($input);
        $searchCondition = $search->condition($input, $qFields);

        $qtyResults = DbUtils::numRows('users', $searchCondition);

        $data['settings'] = $settings;
        $data['filters'] = $filters;
        $data['results'] = $this->searchResults($searchCondition, $settings);
        $data['getString'] = $search->inputToGetString($input);
        $data['qtyResults'] = $qtyResults;
        $data['settings']['maxPage'] = ($qtyResults > 0) ? ceil($qtyResults / $settings['perPage']) : 1;

        return $data;
    }

    /**
     * Listado de registros filtrados por la condición y según los requerimientos definidos
     * 2023-02-12
     */
    function searchResults($searchCondition, $searchSettings)
    {
        $select = $this->select($searchSettings['selectFormat']);
        
        $builder = $this->builder();
        $query = $builder->select($select)
            ->where($searchCondition)
            ->limit($searchSettings['perPage'], $searchSettings['offset'])
            ->orderBy($searchSettings['orderField'], $searchSettings['orderType'])
            ->get();

        $list = $query->getResult(); 

        return $list;
    }

// Datos
//-----------------------------------------------------------------------------

    /**
     * Row de un usuario, tabla users
     * 2023-04-30
     */
    public function getRow($idCode, $selectFormat = 'default')
    {
        $row = NULL;
        $idCodeChecked = 0;
        if ( strlen($idCode) > 0 ) { $idCodeChecked = $idCode; }

        $builder = $this->builder();
        $builder->select($this->select($selectFormat));
        $builder->where('idcode', $idCodeChecked);
        $query = $builder->get();

        if ( $query->getRow(0) ) { $row = $query->getRow(0); }

        return $row;
    }

    public function basic($row)
    {
        $data['row'] = $row;
        $data['headTitle'] = $row->display_name;

        return $data;
    }

    /**
     * Convierte el array de input de un formulario (POST) en un array para
     * crear o actualizar un registro en la tabla users
     * 2025-10-04
     */
    public function inputToRow($input)
    {
        $aRow = $input;
        $aRow['updater_id'] = $_SESSION['user_id'];
        
        //Creación de usuario
        if ( !isset($aRow['id']) ) {
            // Si tiene nombre y apellido y no tiene display_name, crear display_name
            if ( isset($aRow['first_name']) AND isset($aRow['last_name']) AND !isset($aRow['display_name']) ) {
                $aRow['display_name'] = trim($aRow['first_name'] . ' ' . $aRow['last_name']);
            }

            $aRow['creator_id'] = $_SESSION['user_id'];
            $aRow['username'] = $this->emailToUsername($aRow['email']);

            //Control de rol de usuario, no administrador
            if ( intval($aRow['role']) <= 2 ) {
                $aRow['role'] = 21;
            }
            //Encriptar contraseña
            if ( isset($aRow['password']) ) {
                $aRow['password'] = $this->cryptPassword($aRow['password']);
            }
        }

        return $aRow;
    }

// Herramientas
//-----------------------------------------------------------------------------

    /**
     * Genera un username a partir de un email
     * 2022-05-07
     */
    function emailToUsername($email)
    {
        $username = explode('@', $email)[0];
        $username = substr($username, 0,25);
        //Evitar que tenga menos de 8 caracteres:
        if ( strlen($username) < 8 ) {
            $username = substr($username . date('Ymd'),0,8);
        }
        $username = preg_replace('[A-Za-z0-9_]', '', $username);
        $username = DbUtils::uniqueSlug($username, 'users', 'username');
        $username = str_replace(array('.', '-'), '', $username);

        return $username;
    }

// Passwords
//-----------------------------------------------------------------------------

    /**
     * Devuelve password encriptado
     * 2021-02-26
     * @param string $input :: texto contraseña sin encriptar
     * @return string contraseña encriptada
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

// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Elimina registro tabla users, con idcode especificado
     * 2023-02-19
     * @param int $idCode valor en users.idcode
     * @return $result
     */
    public function deleteByIdCode($idCode)
    {
        $restriction = $this->deleteRestriction($idCode);

        if ( strlen($restriction) == 0 ) {
            //No hay restricción, eliminar
            $result = $this->where('idcode',$idCode)->delete();
        } else {
            //Devolver texto de restricción
            $result = $restriction;
        }

        return $result;
    }

    /**
     * Devuelve restricción, si existe alguna para eliminar un usuario
     * Si no existe devuelve cadena vacía, se puede eliminar usuario.
     * 2023-02-19
     * 
     * @return string $restriction
     */
    public function deleteRestriction($idCode)
    {
        $restriction = '';
        $user = $this->getRow($idCode, 'admin');

        if ( is_null($user) ) {
            $restriction .= "Usuario {$idCode} no existe. ";
        } else {
            //Usuario rol developer, no eliminable
            if ( $user->role == 1) $restriction .= "El rol del usuario no permite su eliminación. ";
            //Usuario rol admin, no eliminable
            if ( $user->role == 2) $restriction .= "El rol del usuario no permite su eliminación. ";
        }

        return trim($restriction);
    }

// GESTIÓN DE IMÁGENES
//-----------------------------------------------------------------------------

    /**
     * Asigna imagen a un usuario
     * 2026-01-30
     */
    public function setPicture($idCode, $fileRow)
    {
        $user = $this->getRow($idCode, 'default');
        if ( is_null($user) ) {
            return 0;
        }
        log_message('debug', 'File row: ' . print_r($fileRow, true));
        $result = $this->update($user->id, [
            'image_id' => $fileRow['id'],
            'url_image' => $fileRow['url'],
            'url_thumbnail' => $fileRow['url_thumbnail'],
        ]);

        if ( $result ) {
            return $fileRow['id'];
        } else {
            return 0;
        }
    }
}