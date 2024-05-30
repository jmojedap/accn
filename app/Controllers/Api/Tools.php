<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ItemModel;

class Tools extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		//$this->ItemModel = new ItemModel();
	}

    public function index()
    {
        return redirect()->to('items/explore');
    }

// Exploración de items
//-----------------------------------------------------------------------------

    /**
     * JSON
     * @return string $uniqueSlug :: Slug único para un texto en una tabla y campo
     * 2024-05-30
     */
    public function getUniqueSlug()
    {   
        $request = \Config\Services::request();
        $input = $request->getPostGet();
        
        $text = $input['text'];
        $table = $input['table'];
        $field = $input['field'];
        
        $uniqueSlug = $this->dbTools->uniqueSlug($text, $table, $field);

        return $this->response->setJSON($uniqueSlug);
    }
}