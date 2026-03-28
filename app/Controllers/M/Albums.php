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

    /**
     * Vista pública de un álbum
     * 2026-03-28
     */
    public function images($albumId)
    {
        $data['row'] = $this->albumModel->getRow($albumId);
        $data['images'] = $this->albumModel->images($albumId);

        $data['headTitle'] = $data['row']->title;
        $data['viewA'] = $this->viewsFolder . 'images/images';
        $data['pageTitle'] = $data['row']->title;
        return view(TPL_PUBLIC . 'main', $data);
    }

    /**
     * Vista para editar un álbum
     * 2026-03-22
     */
    public function edit($albumId)
    {
        $data['row'] = $this->albumModel->getRow($albumId);
        $data['headTitle'] = $data['row']->title . ' - Editar álbum';
        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = 'm/sits/edit_albums/' . $data['row']->parent_id;
        $data['pageTitle'] = $data['row']->title;

        return view(TPL_PUBLIC . 'main', $data);
    }

    /**
     * Vista para editar las imágenes de un álbum
     * 2026-03-22
     */
    public function editImages($albumId)
    {
        $data['row'] = $this->albumModel->getRow($albumId);
        $data['images'] = $this->albumModel->images($albumId);

        $data['headTitle'] = $data['row']->title . ' - Editar álbum';
        $data['viewA'] = $this->viewsFolder . 'edit_images/edit_images';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = 'm/sits/edit_albums/' . $data['row']->parent_id;
        $data['pageTitle'] = $data['row']->title;
        return view(TPL_PUBLIC . 'main', $data);
    }
}
