<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SyncModel;
use App\Libraries\DbUtils;

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
     * Genera los archivos JSON de la tabla indicada
     * @param string $tableName
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function generateFiles($tableName = 'items')
    {
        $data = $this->syncModel->exportTableToJSON($tableName);
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

    /**
     * Obtiene el estado de las tablas de la base de datos, incluyendo
     * información sobre los archivos JSON generados y la última fecha
     * de sincronización.
     * 2026-06-07
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function dbTablesStatus()
    {
        $this->response->setHeader(
            'Access-Control-Allow-Origin',
            'http://localhost'
        );

        $data = $this->syncModel->getDbTablesStatus();
        return $this->response->setJSON($data);
    }

    public function updateTablesStatus()
    {
        $data = $this->syncModel->updateTablesStatus();
        return $this->response->setJSON($data);
    }

    /**
     * Actualiza el estado de las tablas en la base de datos local, obteniendo la 
     * información desde la función dbTablesStatus() y guardándola en la tabla sis_tables.
     * 2026-06-07
     */
    public function updateRowsCount()
    {
        //$data['updating_server_rows_count'] = $this->syncModel->updateServerTablesRowsCount();
        $data['updating_local_rows_count'] = $this->syncModel->updateTablesRowsCount();
        
        //$data = $this->syncModel->updateServerTablesStatus();
        return $this->response->setJSON($data);
    }

    public function updateLocalTablesStatus()
    {
        $tablesStatus = $this->syncModel->getDbTablesStatus();

        // Actualizar la tabla sis_tables con la información obtenida
        foreach ($tablesStatus as $table) {
            $rowData = [
                'name' => $table['name'],
                'server_row_count' => $table['server_row_count'],
                'last_sync_at' => $table['last_sync_at']
            ];
            DbUtils::saveRow('sis_tables', "name = '{$table['name']}'", $rowData);
        }

        return $this->response->setJSON([
            'message' => 'Estado de tablas actualizado correctamente',
            'updated_tables' => $tablesStatus
        ]);
    }
}