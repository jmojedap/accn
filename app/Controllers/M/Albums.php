<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;
use App\Models\AlbumModel;
use App\Libraries\DbUtils;

class Albums extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        $this->albumModel = new AlbumModel();
		$this->viewsFolder = 'm/albums/';
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function edit($albumId)
    {
        $data['row'] = $this->albumModel->getRow($albumId);
        $data['headTitle'] = $data['row']->title . ' - Editar álbum';
        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        return view(TPL_PUBLIC . 'main', $data);
    }

    public function editImages($albumId)
    {
        $data['row'] = $this->albumModel->getRow($albumId);
        $data['images'] = $this->albumModel->images($albumId);

        $data['headTitle'] = $data['row']->title . ' - Editar álbum';
        $data['viewA'] = $this->viewsFolder . 'edit_images/edit_images';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        return view(TPL_PUBLIC . 'main', $data);
    }
}
