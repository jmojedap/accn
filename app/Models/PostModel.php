<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class PostModel extends Model
{
    protected $table      = 'posts';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'idcode', 
        'title', 'slug', 'content', 'content_json', 'keywords', 'excerpt',
        'type_id', 'status', 'published_at',
        'image_id', 'url_image', 'url_thumbnail',
        'creator_id', 'updater_id'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules    = [
        'title' => 'required|min_length[3]|max_length[255]',
        'slug' => 'required|is_unique[posts.slug,id,{id}]|min_length[3]|max_length[255]',
    ];
    protected $validationMessages = [
        'slug' => [
            'is_unique' => 'El slug ya está registrado'
        ]
    ];
    protected $skipValidation     = false;


// Buscador
//-----------------------------------------------------------------------------

    /**
     * Segmento SQL SELECT para construir consulta
     * 2024-05-24
     */
    function select($format = 'default')
    {
        $arrSelect['default'] = 'id, idcode, title, slug, excerpt, content, type_id, status, published_at,
            keywords, url_image, url_thumbnail,
            updater_id, creator_id, updated_at, created_at';
        $arrSelect['edition'] = 'id, idcode, published_at, title, slug, excerpt, content, type_id, status, published_at,
            keywords, url_image, url_thumbnail,
            updater_id, creator_id, updated_at, created_at';
        $arrSelect['basic'] = 'id, title, slug, status, type_id, published_at';
        $arrSelect['admin'] = '*';

        return $arrSelect[$format];
    }

    /**
     * Devuelve listado de posts filtrados o segmentados por criterios en $input
     * @param array $input
     * @return array $data
     * 2024-05-24
     */
    public function search($input)
    {
        $qFields = ['title', 'content', 'keywords'];
        $filtersNames = ['q', 'type_id__eq'];
        
        $search = new \App\Libraries\Search();
        $filters = $search->filters($input, $filtersNames);
        $settings = $search->settings($input);
        $searchCondition = $search->condition($input, $qFields);

        $qtyResults = DbUtils::numRows('posts', $searchCondition);

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
     * Row de un post, tabla posts
     * 2024-05-24
     */
    public function getRow($rowId, $selectFormat = 'default')
    {
        $row = NULL;

        $builder = $this->builder();
        $builder->select($this->select($selectFormat));
        $builder->where('id', $rowId);
        $query = $builder->get();

        if ( $query->getRow(0) ) { $row = $query->getRow(0); }

        return $row;
    }

    /**
     * Row de un post identificado por condición WHERE, tabla posts
     * 2026-01-19
     */
    public function getRowByCondition($condition, $selectFormat = 'default')
    {
        $row = NULL;

        $builder = $this->builder();
        $builder->select($this->select($selectFormat));
        $builder->where($condition);
        $query = $builder->get();

        if ( $query->getRow(0) ) { $row = $query->getRow(0); }

        return $row;
    }

    /**
     * Datos básicos de un post, para utilizarse en una vista
     * 2026-01-19
     */
    public function basic($row)
    {
        $data['row'] = $row;
        $data['headTitle'] = $row->title;

        return $data;
    }

    /**
     * Convierte el array de input de un formulario (POST) en un array para
     * crear o actualizar un registro en la tabla posts
     * 2025-10-04
     */
    public function inputToRow($input)
    {
        $aRow = $input;
        $aRow['updater_id'] = $_SESSION['user_id'];
        
        //Creación de post
        if ( !isset($aRow['id']) ) {
            $aRow['creator_id'] = $_SESSION['user_id'];
        }
        
        // Generar Slug si no existe y existe title
        if ( empty($aRow['slug']) && !empty($aRow['title']) ) {
            $aRow['slug'] = DbUtils::uniqueSlug($aRow['title'], 'posts', 'slug');
        }

        return $aRow;
    }

// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Elimina registro tabla posts, con idcode especificado
     * 2023-02-19
     * @param int $idCode valor en posts.idcode
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
     * Devuelve restricción, si existe alguna para eliminar un post
     * Si no existe devuelve cadena vacía, se puede eliminar post.
     * 2026-01-19
     * 
     * @return string $restriction
     */
    public function deleteRestriction($idCode)
    {
        $restriction = '';
        $post = $this->getRowByCondition(['idcode' => $idCode], 'admin');

        if ( is_null($post) ) {
            $restriction .= "Post {$idCode} no existe. ";
        }

        return trim($restriction);
    }

// IMAGES
//-----------------------------------------------------------------------------

    /**
     * Imágenes asociadas al post
     * 2026-01-28
     */
    public function images($postId)
    {
        $builder = $this->db->table('files');
        $builder->select('id, title, url, url_thumbnail, is_main, position');
        $builder->where('is_image', 1);
        $builder->where('table_id', 2000);      //Tabla post
        $builder->where('related_1', $postId);   //Relacionado con el post
        $builder->orderBy('position', 'ASC');
        $query = $builder->get();

        return $query;
    }

    /**
     * Establecer una imagen asociada a un post como la imagen principal (tabla file)
     * 2026-01-28
     */
    public function setMainImage($postId, $fileId)
    {
        $data = ['status' => 0];

        $rowFile = DbUtils::rowId('files', $fileId);
        if (!is_null($rowFile)) {
            // Quitar otro principal
            $this->db->table('files')
                ->where('table_id', 2000)
                ->where('related_1', $postId)
                ->where('is_main', 1)
                ->update(['is_main' => 0]);

            // Poner nuevo principal
            $this->db->table('files')
                ->where('id', $fileId)
                ->where('related_1', $postId)
                ->update(['is_main' => 1]);

            // Actualizar registro en tabla post
            $arrRow = [
                'image_id'      => $rowFile->id,
                'url_image'     => $rowFile->url,
                'url_thumbnail' => $rowFile->url_thumbnail,
            ];

            $this->db->table('posts')
                ->where('id', $postId)
                ->update($arrRow);

            $data['status'] = 1;
        }

        return $data;
    }
}