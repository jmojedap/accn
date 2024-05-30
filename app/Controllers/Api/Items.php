<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ItemModel;

class Items extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->ItemModel = new ItemModel();
	}

    public function index()
    {
        return redirect()->to('items/explore');
    }

// Exploración de items
//-----------------------------------------------------------------------------

    /**
     * JSON
     * Buscar items según filtros y condiciones solicitadas
     * 
     */
    public function search()
    {   
        $request = \Config\Services::request();
        $input = $request->getPostGet();
        
        $search = new \App\Libraries\Search();

        $data = $this->ItemModel->search($input);

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Buscar items según filtros y condiciones solicitadas
     * 
     */
    public function getList($categoryCode = 0)
    {   
        $request = \Config\Services::request();
        $input = $request->getPostGet();
        
        $search = new \App\Libraries\Search();
        $settings = $search->settings($input);
        $settings['selectFormat'] = 'admin';
        $searchCondition = "category_id = {$categoryCode}";

        $items = $this->ItemModel->searchResults($searchCondition, $settings);

        return $this->response->setJSON($items);
    }

    /**
     * JSON
     * Validar formularios de creación y actualización de datos de items
     * 2023-02-04
     */
    public function validateForm()
    {
        $data = ['status' => 1, 'error' => null];

        $data['status'] = ( $data['error'] ) ? 0 : 1 ;

        return $this->response->setJSON($data);
    }

    /**
     * AJAX - JSON
     * Insertar un nuevo registro en la tabla items
     * 2023-01-29
     */
    public function create()
    {
        //Preparar array del registro
        $input = $this->request->getPost();
        $aRow = $this->ItemModel->inputToRow($input);

        //Guardar
        $data['savedId'] = $this->ItemModel->insert($aRow);

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['itemId'] = $this->dbTools->setitemId('items', $data['savedId']);
            $data['aRow'] = $aRow;
        }
        
        $data['errors'] = $this->ItemModel->errors();

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Crear o actualizar los datos de un ítem, tabla items
     * 2024-05-30
     */
    public function save($itemId = 0)
    {
        $input = $this->request->getPost();
        $aRow = $this->ItemModel->inputToRow($input);
        $condition = "id = {$itemId}";

        $data['savedId'] = $this->dbTools->saveRow('items', $condition, $aRow);
        $data['errors'] = $this->ItemModel->errors();

        /*if ( $data['savedId'] > 0 ) {
            $data['savedId'] = $aRow['id'];
        } else {
        }*/

        return $this->response->setJSON($data);
    }

// Eliminación
//-----------------------------------------------------------------------------

    /**
     * Eliminación de un registro tabla Item
     * @param int $itemId :: Id del item, item.id
     * @param int $categoryId :: Categoría del ítem, item.category_id
     * @return array $data :: Resultado de la eliminación
     * 2024-05-29
     */
    public function deleteRow($itemId, $categoryId)
    {
        $condition = "id = {$itemId} AND category_id = {$categoryId}";
        $data['result'] = $this->ItemModel->deleteByCondition($condition);

        return $this->response->setJSON($data);
    }

    /**
     * Eliminación de items seleccionados
     * 2023-02-18
     */
    public function deleteSelected()
    {
        $selected = explode(',', $this->request->getPost('selected'));
        $results = [];
        
        foreach ($selected as $itemId) {
            $results[$itemId] = $this->ItemModel->deleteByitemId($itemId);
        }

        $data['results'] = $results;
        return $this->response->setJSON($data);
    }
}