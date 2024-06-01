<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\itemModel;

class Items extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->itemModel = new itemModel();
        $this->viewsFolder = 'admin/items/';
        $this->backLink = URL_ADMIN . 'items/values';
        $this->entityInfo = [
            'controller' => 'items',
            'singular' => 'Ítem',
            'plural' => 'Ítems',
            'isMale' => 1,
        ];
	}

    public function index()
    {
        return redirect()->to('admin/items/explore');
    }

// Exploración de usuarios
//-----------------------------------------------------------------------------

    /**
     * HTML
     * Listado de exploración y búsqueda de usuarios
     * 2023-03-05
     */
    public function values($categoryCode = 121, $scope = '')
    {
        $categoriesCondition = 'category_id = 0';
        if ( $scope != '' ) {
            $categoriesCondition .= " AND filters LIKE '%-{$scope}-%'";
        }
        $data['arrCategories'] = $this->itemModel->arrOptions($categoriesCondition);
        $data['arrScopes'] = $this->itemModel->arrOptions('category_id = 29');
        $data['entityInfo'] = $this->entityInfo;
        
        $data['headTitle'] = 'Valores de parámetros';
        $data['viewA'] = $this->viewsFolder . 'values/values';
        $data['categoryCode'] = $categoryCode;
        $data['scope'] = $scope;
        
        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}