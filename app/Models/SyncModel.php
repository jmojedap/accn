<?php namespace App\Models;

use CodeIgniter\Model;

class SyncModel extends Model
{

    /**
     * Exportar tabla a JSON en partes de N registros.
     *
     * @param string $table_name Nombre de la tabla
     * @param int    $chunkSize  Número de registros por archivo (default: 10000)
     * @return array Resultado con mensaje y lista de archivos
     */
    public function exportTableToJSON(string $table_name, int $chunkSize = 10000): array
    {
        $db = db_connect();

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

            $fileName = "part_{$part}.json";
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
            'qty_files'   => count($files)
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

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;

            $filePath = $folderPath . $file;
            $json = file_get_contents($filePath);
            $records = json_decode($json, true);

            if (!is_array($records)) continue;

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

}