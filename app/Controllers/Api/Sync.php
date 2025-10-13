<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SyncModel;

class Sync extends BaseController
{
    protected $syncModel;

    public function __construct()
	{
		$this->db = \Config\Database::connect();
        $this->syncModel = new SyncModel();
	}

// Funciones
//-----------------------------------------------------------------------------

    /**
     * Genera los archivos CSV de la tabla indicada
     * @param string $tableName
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function generateFiles($tableName = 'items')
    {
        $data = $this->syncModel->exportTableToJSON($tableName, 20);
        $data['table_name'] = $tableName;

        return $this->response->setJSON($data);
    }

    /**
     * Elimina los archivos JSON generados para una tabla específica.
     * @param string $tableName Nombre de la tabla
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 2025-10-04
     */
    public function deleteGeneratedFiles($tableName = 'items')
    {
        $data = $this->syncModel->deleteGeneratedFiles($tableName);
        $data['table_name'] = $tableName;

        return $this->response->setJSON($data);
    }

    /**
     * Actualizar registros de una tabla, con los archivos JSON descargados
     * 2025-10-04
     */
    public function syncTable($tableName = 'items')
    {
        $data = $this->syncModel->syncTable($tableName);
        $data['table_name'] = $tableName;

        return $this->response->setJSON($data);
    }
}