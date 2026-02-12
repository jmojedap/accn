<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FileModel;

class Files extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->fileModel = new FileModel();
        $this->viewsFolder = 'admin/files/';
        $this->backLink = URL_ADMIN . 'files/explore';
        $this->entityInfo = [
            'controller' => 'files',
            'singular' => 'Archivo',
            'plural' => 'Archivos',
            'isMale' => 1,
        ];
	}

    public function index($fileId = null)
    {
        if ( $fileId > 0 ) {
            return redirect()->to("admin/files/info/{$fileId}");
        } else {
            return redirect()->to('admin/files/explore');
        }
    }

// Exploración de archivos
//-----------------------------------------------------------------------------

    /**
     * HTML VIEW
     * Listado de exploración y búsqueda de archivos
     * 2023-03-05
     */
    public function explore()
    {
        //Filtros y detalle de la búsqueda
        $input = $this->request->getPost();
        $data['search'] = $this->fileModel->search($input);
        
        $data['entityInfo'] = $this->entityInfo;
        $data['headTitle'] = $this->entityInfo['plural'];
        $data['viewsFolder'] = $this->viewsFolder . 'explore/';
        $data['viewA'] = $this->viewsFolder . 'explore/explore';
        $data['nav2'] = $this->viewsFolder . 'menus/general';

        $data['table'] = $this->fileModel->table;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Vista información del archivo
     * @param int $rowId :: Id de la tabla files, identifica al registro del archivo
     * 
     * 2023-01-28
     */
    public function info($rowId)
    {
        $row = $this->fileModel->getRow($rowId, 'admin');
        $data = $this->fileModel->basic($row);

        $data['viewA'] = $this->viewsFolder . 'info';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Vista información detallada del registro archivo
     * @param int $rowId :: Id de la tabla files, identifica al registro del archivo
     * 2023-04-17
     */
    public function details($rowId)
    {
        $file = $this->fileModel->getRow($rowId, 'admin');
        $data = $this->fileModel->basic($file);

        $fieldsMeta = $this->fileModel->getFieldData('files');
        $data['fieldsMeta'] = $fieldsMeta;

        $data['row'] = $file;
        $data['viewA'] = 'common/bs5/row_edit';
        $data['formAction'] = URL_API . 'files/update/' . $file->id;
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML FORM
     * Vista formulario para subir un archivo y crear registro en la tabla
     * files
     * 2023-01-28
     */
    public function add()
    {
        $data['headTitle'] = 'Cargar archivo';
        $data['viewA'] = $this->viewsFolder . 'add/add';
        $data['nav2'] = $this->viewsFolder . 'menus/general';
        $data['entityInfo'] = $this->entityInfo;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Formulario para la edición de archivos
     * 2023-02-04
     */
    public function edit($rowId)
    {
        $row = $this->fileModel->getRow($rowId);
        $data = $this->fileModel->basic($row);

        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Formulario para el recorte de imágenes
     * 2026-02-11
     */
    public function cropping($rowId)
    {
        $row = $this->fileModel->getRow($rowId);
        $data = $this->fileModel->basic($row);

        $data['imageId'] = $rowId;
        $data['backDestination'] = URL_ADMIN . 'files/info/' . $rowId;
        $data['urlImage'] = $row->url;

        $data['viewA'] = 'common/bs5/cropping';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;


        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}