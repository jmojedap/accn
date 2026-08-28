<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class SyncModel extends Model
{

    protected $table      = 'sis_tables';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'description', 'server_row_count', 'local_row_count', 
        'last_sync_at', 'sync_chunk_size'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules = [
        'name' => 'required',
    ];
    protected $skipValidation     = false;

    /**
     * Tables info desde la tabla sis_tables, con el número de registros en cada tabla (server_row_count)
     * 2026-06-07
     * @return array
     */
    public function tablesInfo()
    {
        $db = db_connect();
        $tables = $db->table('sis_tables')->get()->getResultArray();

        return $tables;
    }


    /**
     * Exportar tabla a JSON en partes de N registros.
     *
     * @param string $table_name Nombre de la tabla
     * @param int    $chunkSize  Número de registros por archivo (default: 10000)
     * @return array Resultado con mensaje y lista de archivos
     */
    public function exportTableToJSON(string $table_name, int $chunkSize = 10000): array
    {
        //Limpiar carpeta antes de generar
        $this->deleteGeneratedFiles($table_name);

        $db = db_connect();

        //Prefijo de 24 caracteres alfanuméricos aleatorios para el nombre de archivos 
        $prefix = substr(md5(uniqid(rand(), true)), 0, 24);

        // 📂 Carpeta destino en public/content/database/{tableName}/
        $folderPath = FCPATH . "public/content/database/" . $table_name . "/";
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // Total de registros y número de partes
        $total = $db->table($table_name)->countAll();
        $parts = (int) ceil($total / $chunkSize);

        $files = [];

        for ($part = 1; $part <= $parts; $part++) {
            $offset = ($part - 1) * $chunkSize;

            $rows = $db->table($table_name)
                    ->limit($chunkSize, $offset)
                    ->get()
                    ->getResultArray();

            $fileName = "{$prefix}_{$part}.json";
            $filePath = $folderPath . $fileName;

            // Guardar con formato legible
            $jsonData = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($filePath, $jsonData);

            // Ruta relativa accesible desde la web
            //$files[] = "content/database/{$table_name}/{$fileName}";
            $files[] = $fileName;
            //log_message('debug', "Archivo JSON generado: {$filePath}");
        }

        return [
            'message' => "✅ Exportación completa: {$parts} archivo(s) JSON generado(s)",
            'files'   => $files,
            'qty_files'   => count($files),
            'prefix'   => $prefix
        ];
    }

    /**
     * Sincroniza los archivos JSON de una carpeta con la tabla correspondiente.
     * Si el registro existe (por id), lo actualiza; si no, lo inserta.
     * 2025-10-04
     *
     * @param string $tableName  Nombre de la tabla
     * @param string $primaryKey Nombre de la llave primaria (por defecto 'id')
     * @return array
     */
    public function syncTable(string $tableName, string $primaryKey = 'id'): array
    {
        helper('filesystem');

        $folderPath = FCPATH . "public/content/database/" . $tableName . "/";

        if (!is_dir($folderPath)) {
            return [
                'status' => 'error',
                'message' => "❌ No existe la carpeta: {$folderPath}",
                'inserted' => 0,
                'updated'  => 0,
            ];
        }

        
        $files = get_filenames($folderPath, false);
        $inserted = 0;
        $updated  = 0;
        
        $db = db_connect();

        // Eliminar registros antes de sincronizar
        $db->table($tableName)->truncate();

        //Recorrer cada archivo JSON en la carpeta
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;

            $filePath = $folderPath . $file;
            $json = file_get_contents($filePath);
            $records = json_decode($json, true);

            if (!is_array($records)) continue;

            //Recorrer cada registro del archivo JSON
            foreach ($records as $record) {
                if (!isset($record[$primaryKey])) continue;

                // Verificar si existe
                $exists = $db->table($tableName)
                            ->where($primaryKey, $record[$primaryKey])
                            ->get()
                            ->getRowArray();

                if ($exists) {
                    $db->table($tableName)
                    ->where($primaryKey, $record[$primaryKey])
                    ->update($record);
                    $updated++;
                } else {
                    $db->table($tableName)->insert($record);
                    $inserted++;
                }
            }
        }

        return [
            'status' => 'success',
            'message' => "✅ Sincronización completada para {$tableName}",
            'inserted' => $inserted,
            'updated'  => $updated,
        ];
    }

    /**
     * Elimina los archivos JSON generados para una tabla específica.
     * 2025-10-04
     *
     * @param string $tableName Nombre de la tabla
     * @return array Resultado con mensaje y cantidad de archivos eliminados
     */
    public function deleteGeneratedFiles(string $tableName): array
    {
        helper('filesystem');

        // Normaliza y construye la ruta completa
        $folderPath = rtrim(FCPATH . "public/content/database/" . $tableName . "/", '/\\') . '/';

        // Validar existencia de carpeta
        if (!is_dir($folderPath)) {
            return [
                'status' => 'error',
                'message' => "❌ No existe la carpeta: {$folderPath}",
                'deleted_files' => 0
            ];
        }

        $files = get_filenames($folderPath, false);
        $deletedCount = 0;
        $errors = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
                continue; // ignorar otros archivos
            }

            $filePath = $folderPath . $file;

            // Validar existencia antes de intentar eliminar
            if (is_file($filePath)) {
                if (@unlink($filePath)) {
                    $deletedCount++;
                } else {
                    $errors[] = basename($file);
                }
            }
        }

        // Mensaje resumen
        $message = "✅ Eliminación completa: {$deletedCount} archivo(s) eliminado(s)";
        if (!empty($errors)) {
            $message .= " ⚠️ No se pudieron eliminar: " . implode(', ', $errors);
        }

        return [
            'status' => empty($errors) ? 'success' : 'partial',
            'message' => $message,
            'deleted_files' => $deletedCount,
            'failed_files' => $errors
        ];
    }

    /**
     * Obtiene el estado de las tablas de la base de datos y el número de registros
     * 2026-06-07
     * @return array
     */
    public function getDbTablesStatus()
    {
        $db = db_connect();
        $tablesStatus = [];

        foreach ($db->listTables() as $tableName) {
            $tablesStatus[] = (object) [
                'name' => $tableName,
                'num_rows' => $db->table($tableName)->countAll(),
            ];
        }

        return $tablesStatus;
    }

    /**
     * Actualiza la cantidad de registros de cada tabla en el servidor, 
     * pone los valores en la tabla sis_tables.
     * 2026-06-07
     */
    public function updateServerTablesRowsCount()
    {
        // Cargar la librería Pml para usar funcion de llamar datos de url, 
        $this->pml = new \App\Libraries\Pml();

        $url = URL_SYNC . 'db_tables_status';
        $tablesData = $this->pml->getUrlContent($url);
        $tablesData = json_decode($tablesData, true);

        log_message('debug', "URL consultada para actualizar filas: {$url}");
        
        $data['updated_tables'] = [];

        // Actualizar la tabla sis_tables con la información obtenida
        foreach ($tablesData as $table) {
            $rowData = [
                'name' => $table['name'],
                'server_row_count' => $table['num_rows'],
            ];
            $data['updated_tables'][] = DbUtils::saveRow('sis_tables', "name = '{$table['name']}'", $rowData);
        }

        return $data;
    }

    public function updateTablesRowsCount()
    {
        $tablesData = $this->getDbTablesStatus();

        $data['updated_tables'] = [];
        
        foreach ($tablesData as $table) {
            $rowData = [
                'name' => $table->name,
                'local_row_count' => $table->num_rows,
            ];
            $data['updated_tables'][] = DbUtils::saveRow('sis_tables', "name = '{$table->name}'", $rowData);
        }

        return $data;
    }

}
