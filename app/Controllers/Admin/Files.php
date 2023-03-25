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
        //$this->viewsFolderAdmin = 'admin/files/';
        $this->backLink = URL_ADMIN . 'files/explore';
        $this->entityInfo = [
            'controller' => 'files',
            'singular' => 'Archivo',
            'plural' => 'Archivos',
            'isMale' => 1,
        ];
	}

    public function index()
    {
        return redirect()->to('admin/files/explore');
    }

// Exploración de archivos
//-----------------------------------------------------------------------------

    /**
     * HTML
     * Listado de exploración y búsqueda de archivos
     * 2023-03-05
     */
    public function explore()
    {
        //Filtros y detalle de la búsqueda
        $request = \Config\Services::request();
        $input = $request->getGet();
        //$input['perPage'] = 2;
        $data['search'] = $this->fileModel->search($input);
        
        $data['entityInfo'] = $this->entityInfo;
        $data['headTitle'] = $this->entityInfo['plural'];
        $data['viewsFolder'] = $this->viewsFolder . 'explore/';
        $data['viewA'] = $this->viewsFolder . 'explore/explore';
        $data['nav2'] = $this->viewsFolder . 'menus/general';

        $data['table'] = $this->fileModel->table;

        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Vista información del archivo
     * 2023-01-28
     */
    public function info($rowId)
    {
        $row = $this->fileModel->get($rowId, 'basic');
        $data = $this->fileModel->basic($row);

        $data['viewA'] = $this->viewsFolder . 'info';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML FORM
     * Vista con formulario para crear un nuevo archivo
     * 2023-01-28
     */
    public function add()
    {
        $data['headTitle'] = 'Cargar archivo';
        $data['viewA'] = $this->viewsFolder . 'add/add';
        $data['nav2'] = $this->viewsFolder . 'menus/general';
        $data['entityInfo'] = $this->entityInfo;

        //$data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Formulario para la edición de archivos
     * 2023-02-04
     */
    public function edit($rowId)
    {
        $row = $this->fileModel->get($rowId);
        $data = $this->fileModel->basic($row);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');
        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}