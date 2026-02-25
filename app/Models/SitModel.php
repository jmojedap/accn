<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class SitModel extends PostModel
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

    /**
     * Obtiene los sitios creados por un usuario
     * @param int $userId
     * @return array
     */
    public function mySits($userId)
    {
        $this->where('creator_id', $userId);
        $this->where('type_id', 101);
        $this->orderBy('created_at', 'desc');
        return $this->findAll();
    }

    /**
     * Obtiene los álbumes de fotos de un sitio
     * @param int $sitId
     * @return array
     */
    public function albums($sitId)
    {
        $this->select('id, idcode, title, slug, url_image, url_thumbnail');
        $this->where('parent_id', $sitId);
        $this->where('type_id', 5);
        $this->orderBy('title', 'asc');
        return $this->findAll();
    }

}