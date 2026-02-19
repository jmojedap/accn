<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\FileModel;

class Files extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->fileModel = new FileModel();
	}

    public function index()
    {
        return redirect()->to('files/explore');
    }

// Exploración de archivos
//-----------------------------------------------------------------------------

    /**
     * JSON
     * Buscar archivos según filtros y condiciones solicitadas
     * 2024-05-26
     */
    public function search()
    {   
        $input = $this->request->getPost();
        $data = $this->fileModel->search($input);
        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Actualizar el registro de un archivo, tabla files
     * 2023-04-15
     */
    public function update($fileId)
    {
        $aRow = $this->request->getPost();

        $data['saved'] = $this->fileModel->where('id',$fileId)
                            ->set($aRow)->update();

        if ( $data['saved'] ) {
            $data['savedId'] = $aRow['id'];
            $data['savedId'] = $aRow['id'];
        } else {
            $data['errors'] = $this->fileModel->errors();
        }

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Recortar una imagen
     * 2026-02-11
     */
    public function crop($fileId)
    {
        //Valor inicial por defecto
        $data = ['status' => 0, 'message' => 'No tiene permiso para modificar esta imagen'];
        
        $editable = $this->fileModel->editable($fileId);
        if ( $editable ) { 
            $aCrop = $this->request->getPost();
            $data = $this->fileModel->crop($fileId, $aCrop);
        }
        
        return $this->response->setJSON($data);
    }

// Eliminación
//-----------------------------------------------------------------------------

    /**
     * Eliminación de archivos seleccionados
     * 2023-02-18
     */
    public function deleteSelected()
    {
        $selected = explode(',', $this->request->getPost('selected'));
        $results = [];
        
        $session = $_SESSION;

        foreach ($selected as $fileId) {
            $results[$fileId] = $this->fileModel->deleteUnlink($fileId, $session);
        }

        $data['results'] = $results;

        return $this->response->setJSON($data);
    }

    /**
     * Eliminación de un archivo
     * 2026-02-19
     */
    public function delete($fileId)
    {
        $session = $_SESSION;
        $data['deleting_result'] = $this->fileModel->deleteUnlink($fileId, $session);
        return $this->response->setJSON($data);
    }

// UPLOAD
//-----------------------------------------------------------------------------

	/**
	 * Cargar un archivo y crear registro en la tabla files
	 * 2023-03-25
	 */
	public function upload()
	{
        $userId = $_SESSION['user_id'];
        $data = $this->fileModel->upload($this->request, $userId);

		return $this->response->setJSON($data);
	}

// ORDENACIÓN
//-----------------------------------------------------------------------------

    /**
     * Actualiza el orden de una imagen asociada a un post (tabla file)
     * 2026-01-28
     */
    public function updatePosition($fileId, $newPosition)
    {
        $data = $this->fileModel->updatePosition($fileId, $newPosition);
        return $this->response->setJSON($data);
    }
}