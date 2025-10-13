<?php namespace App\Models;

use CodeIgniter\Model;

class AppModel extends Model
{

    public function view($data)
    {
        $view = \Config\Services::renderer();

        $result['headTitle'] = '';
        $result['viewA'] = '';

        if ( isset($data['viewA']) ) { $result['viewA'] = $view->render($data['viewA'], $data); }

        /*$data['headTitle'] = 'Probando render';
        $data['viewA'] = $view->render('formulario');*/
        return $this->response->setJSON($result);
        
        //return $result;
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


}