<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class FileModel extends Model
{
    protected $table      = 'files';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'file_name', 'ext', 'folder', 'is_image', 'type', 'type_id','title', 'subtitle',
        'description', 'keywords', 'external_link',
        'size', 'width', 'height', 'is_main', 'position',
        'url', 'url_thumbnail', 'table_id', 'related_1',
        'creator_id', 'updater_id'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules = [
        'title' => 'required',
    ];
    protected $skipValidation     = false;


// Buscador
//-----------------------------------------------------------------------------

    /**
     * Array con los datos para la vista de exploración
     */
    public function exploreData($input)
    {
        $data = $this->search($input);

        //Elemento de exploración
            $data['controller'] = 'files';                      //Nombre del controlador
            $data['cf'] = 'files/explore/';                     //Nombre del controlador-función
            $data['viewsFolder'] = 'admin/files/explore/';     //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['headTitle'] = 'Archivos';
            $data['viewA'] = $data['viewsFolder'] . 'explore';
            $data['nav2'] = $data['viewsFolder'] . 'menu';
        
        return $data;
    }

    /**
     * Buscar registros en la tabla files según los filtros y opciones definidas
     * en $input
     * @param array $input :: Filtros y opciones de búsqueda
     * @return array $data :: Resultados de búsqueda y configuración.
     * 2024-05-26
     */
    public function search($input):array
    {
        $qFields = ['file_name', 'title', 'description', 'keywords'];
        $filtersNames = ['q','table_id__eq'];
        
        $search = new \App\Libraries\Search();
        $filters = $search->filters($input, $filtersNames);
        $settings = $search->settings($input);
        $settings['selectFormat'] = 'admin';
        $searchCondition = $search->condition($input, $qFields);
        $qtyResults = DbUtils::numRows('files', $searchCondition);

        $data['settings'] = $settings;
        $data['filters'] = $filters;
        $data['results'] = $this->searchResults($searchCondition, $settings);
        $data['qtyResults'] = $qtyResults;
        $data['maxPage'] = ($qtyResults > 0) ? ceil($qtyResults / $settings['perPage']) : 1;

        return $data;
    }

    /**
     * Segmento SQL SELECT para construir consulta
     * 2020-08-11
     */
    function select($format = 'default')
    {
        $arrSelect['default'] = 'id, file_name, title, url, url_thumbnail, folder, ext, is_image, keywords, description';
        $arrSelect['basic'] = 'id, file_name, title, url, url_thumbnail, description, keywords, is_image';
        $arrSelect['admin'] = '*';

        return $arrSelect[$format];
    }

    /**
     * Listado de registros filtrados por la condición y según los requerimientos definidos
     * 2023-02-12
     */
    function searchResults($searchCondition, $searchSettings)
    {
        $select = $this->select($searchSettings['selectFormat']);
        
        $builder = $this->builder();
        $query = $builder->select($select)
            ->where($searchCondition)
            ->limit($searchSettings['perPage'], $searchSettings['offset'])
            ->orderBy($searchSettings['orderField'], $searchSettings['orderType'])
            ->get();

        $list = $query->getResult(); 

        return $list;
    }

// DATOS DE UN FILE
//-----------------------------------------------------------------------------

    /**
     * Row de un FILE
     * 2023-04-30
     */
    public function getRow($fileId, $selectFormat = 'default')
    {
        $row = NULL;
        $fileIdChecked = 0;
        if ( strlen($fileId) > 0 ) { $fileIdChecked = $fileId; }

        //$db = db_connect();
        $builder = $this->builder();
        $builder->select($this->select($selectFormat));
        $builder->where('id', $fileIdChecked);
        $query = $builder->get();

        if ( $query->getRow(0) ) { $row = $query->getRow(0); }

        return $row;
    }

    public function basic($row)
    {
        $data['row'] = $row;
        $data['headTitle'] = 'Archivo - ' . $row->title;

        return $data;
    }

// UPLOAD
//-----------------------------------------------------------------------------

    /**
     * Guarda el archivo en carpeta de UPLOADS y crea registro en tabla files
     * Devuelve el array con los datos del archivo guardado
     * 2023-04-08
     */
    public function upload($request, $userId)
    {
        $data['savedId'] = 0; //Resultado por defecto

        $validation = \Config\Services::validation();
        $validation->setRuleGroup('generalFile');   //Ver Config/Validation

        //Validar archivo
        if ( $validation->withRequest($request)->run() ) {
            //Guardar archivo y crear registro en tabla files
                $file = $request->getFile('file_field');
                $aRowAdd = $this->aRowAdd($file, $userId, $request);
                $this->insert($aRowAdd);
                $data['savedId']= $this->insertID();
                $data['row'] = $this->getRow($data['savedId']);

                //Guardar archivo en carpeta
                if (! $file->hasMoved()) {
                    $file->move(PATH_UPLOADS . $data['row']->folder, $data['row']->file_name);
                }
            
            //Procesos adicionales para archivos de imagen
            if ( $data['row']->is_image )
            {	
                //Crear miniatura
                $data['thumbnail'] = $this->createThumbnail($data['row']);
                //Reducir dimensiones a un máximo permitido
                $data['resized'] = $this->resizeImage($data['row']);
                //Actualizar los campos de dimensiones y tamaño de archivo
                $data['imageDimensions'] = $this->updateDimensions($data['row']);
            }
        } else {
            $data['errors'] = $validation->getErrors();
        }

        return $data;
    }

    /**
     * Prepara array para crear un nuevo registro en la tabla files a partir
     * del array base, archivo y userId
     * @param object $file archivo cargado
     * @param int $userId ID del usuario que carga el archivo
     * @param object $request
     * @return array $aRow, registro para guardar en la tabla file
     * 2023-03-26
     */
    public function aRowAdd($file, $userId, $request)
    {
        helper('text'); //Para random_string
        
        $aRow = $request->getPost();;
        $fileName = $userId . '_' .  date('YmdHis') . random_string('numeric', 3) . '.' . $file->getExtension();
        $folder = date('Y/m/');

        //Construir registro
        $aRow['title'] = substr($file->getName(),0, strlen($file->getName()) - (strlen($file->getExtension()) + 1));
        $aRow['ext'] = $file->getExtension();
        $aRow['folder'] = $folder;
        $aRow['is_image'] = ( substr($file->getMimeType(),0,5) == 'image' ) ? 1 : 0 ;
        $aRow['type'] = $file->getMimeType();
        $aRow['file_name'] =  $fileName;
        $aRow['size'] = intval($file->getSize()/1028);
        $aRow['url'] = URL_UPLOADS . $folder . $fileName;
        $aRow['url_thumbnail'] = URL_UPLOADS . $folder . 'sm_' . $fileName;
        $aRow['creator_id'] = $userId;
        $aRow['updater_id'] = $userId;
        $aRow['created_at'] = date('Y-m-d H:i:s');
        $aRow['updated_at'] = date('Y-m-d H:i:s');

        return $aRow;
    }

// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Elimina registro de la tabla files y elimina (unlink) archivos asociados 
     * de las carpetas en servidor
     * 2023-04-07
     */
    public function deleteUnlink($fileId, $session)
    {
        $row = $this->getRow($fileId, 'admin');
        $restriction = $this->deleteRestriction($row, $session);

        if ( strlen($restriction) == 0 ) {
            //No hay restricción, eliminar
            $result = $this->where('id',$row->id)->delete();
            if ( $result == TRUE ) {
                $this->unlink($row);

                //Editar registros de tablas relacionadas
                $this->editRelatedRows($row->id);
            }
        } else {
            //Devolver texto de restricción
            $result = $restriction;
        }

        return $result;
    }

    /**
     * Devuelve restricción para eliminar un archivo, si existe
     * Si no existe devuelve cadena vacía, se puede eliminar archivo.
     * 2023-04-04
     * 
     * @return string $restriction
     */
    public function deleteRestriction($row, $session)
    {
        $restriction = '';

        if ( is_null($row) ) {
            $restriction .= "El registro que intenta eliminar no existe. ";
        } else {
            //No administradores ni editores
            if ( $session['role'] > 3 ) {
                //El usuario en sesión no es el creador
                if ( $session['user_id'] != $row->creator_id ) $restriction .= "No puedes eliminar un archivo que no es tuyo. ";
            }
        }

        return trim($restriction);
    }

    /**
     * Eliminar archivo y miniaturas si existen
     * 2021-01-25
     */
    public function unlink($rowFile)
    {
        //Array rutas de archivos
        $paths[] = PATH_UPLOADS . $rowFile->folder . 'sm_' . $rowFile->file_name;   //Thumbnail
        $paths[] = PATH_UPLOADS . $rowFile->folder . $rowFile->file_name;           //Archivo original

        //Eliminar archivos
        foreach ($paths as $path)
        {
            if (  file_exists($path) ) unlink($path);
        }
    }

    /**
     * Editar registros de tablas relacionadas con archivo eliminado
     * 2026-01-30
     */
    public function editRelatedRows($fileId)
    {
        $aRow = ['image_id' => 0, 'url_image' => '', 'url_thumbnail' => ''];

        //Se edita la asociación del archivo en la tabla posts
        $this->db->table('posts')
            ->where('image_id', $fileId)
            ->update($aRow);

        //Se edita la asociación del archivo en la tabla users  
        $this->db->table('users')
            ->where('image_id', $fileId)
            ->update($aRow);
    }   

// COLECCIONES DE ARCHIVOS
//-----------------------------------------------------------------------------

    /**
     * Actualiza la posición de un archivo dentro de su grupo
     */
    public function updatePosition(int $fileId, int $newPosition): array
    {
        $data = ['status' => 0];

        // 1. Obtener el archivo usando el método find() de CI4
        $file = $this->find($fileId);
        if (!$file) return $data;

        // Convertir a objeto si CI4 te devuelve un array (depende de tu config)
        $file = (object)$file;

        // 2. Filtros de grupo
        $whereGroup = [
            'table_id'  => $file->table_id,
            'related_1' => $file->related_1,
            'album_id'  => $file->album_id
        ];

        // 3. Obtener el total (validación de rango)
        $maxPosition = $this->where($whereGroup)->countAllResults();

        if ($newPosition >= 0 && $newPosition < $maxPosition) {
            
            // Usamos la instancia de base de datos para transacciones
            $db = \Config\Database::connect();
            $db->transStart();

            $builder = $this->builder(); // Acceso directo al Query Builder del modelo

            if ($newPosition > $file->position) {
                // Mover hacia abajo
                $builder->where($whereGroup)
                        ->where('position <=', $newPosition)
                        ->where('position >', $file->position)
                        ->set('position', 'position - 1', false)
                        ->update();

            } else if ($newPosition < $file->position) {
                // Mover hacia arriba
                $builder->where($whereGroup)
                        ->where('position >=', $newPosition)
                        ->where('position <', $file->position)
                        ->set('position', 'position + 1', false)
                        ->update();
            }

            // 4. Actualizar la prioridad del archivo objetivo
            // Usamos update() del modelo que ya conoce el primaryKey
            $this->update($fileId, ['position' => $newPosition]);

            $db->transComplete();

            if ($db->transStatus() !== false) {
                $data['status'] = 1;
            }
        }

        return $data;
    }

// GESTIÓN DE IMÁGENES
//-----------------------------------------------------------------------------

    /**
     * Crea la miniatura de una imagen
     * 2023-03-25
     */
    public function createThumbnail($row, $prefix = 'sm_', $pixels = 120)
    {
        $thumbnail_path = PATH_UPLOADS . $row->folder . $prefix . $row->file_name;

        /* IMAGEN NO CUADRADA */
        $fileDimensions = $this->arrDimensions($row);

        //Imagen horizontal
        $height = $pixels;
        $width = intval($pixels * $fileDimensions['width'] / $fileDimensions['height']);

        //Imagen vertical
        if ( $fileDimensions['height'] > $fileDimensions['width'] ) {
            $width = $pixels;
            $height = intval($pixels * $fileDimensions['height'] / $fileDimensions['width']);
        }

        $image = \Config\Services::image()
            ->withFile(PATH_UPLOADS . $row->folder . $row->file_name)
            ->resize($width, $height) //Miniatura no cuadrada
            ->save($thumbnail_path, 90);

        return $thumbnail_path;
    }

    /**
     * Ajusta una imagen cargada con una calidad y tamaño determinado
     * 2021-01-22
     */
    public function resizeImage($row)
    {
        //Valores iniciales
        $resized = FALSE;
        $file_path = PATH_UPLOADS . $row->folder . $row->file_name;
        $max_pixels = 800;      //Tamaño máximo en pixeles
        $master_dim = 'width';  //Lado más largo para conservar tamaño

        $image_size = getimagesize($file_path);

        //Si la imagen es horizontal
        if ( $image_size[0] > $image_size[1] ) $master_dim = 'height';
        
        //Si alguna dimensión supera el máximo
        if ( $image_size[0] > $max_pixels or $image_size[1] > $max_pixels )
        {
            $image = \Config\Services::image()
                ->withFile($file_path)
                ->resize($max_pixels, $max_pixels, TRUE, $master_dim)
                ->save($file_path, 90);

            $resized = TRUE;
        }

        return $resized;
    }

    /**
     * Actualiza campos de dimensiones de una imagen, registro en la tabla files
     * 2023-03-26
     */
    public function updateDimensions($row)
    {
        $arrRow = $this->arrDimensions($row);
        $this->update($row->id, $arrRow);
        return $arrRow;
    }

    /**
     * Array con dimensiónes de un archivo de imagen
     * 2023-03-25
     */
    public function arrDimensions($row)
    {
        $file_path = PATH_UPLOADS . $row->folder . $row->file_name;

        $image_size = getimagesize($file_path);

        $arrDimensions['width'] = $image_size[0];
        $arrDimensions['height'] = $image_size[1];
        $arrDimensions['size'] = intval(filesize($file_path)/1028);    //Tamaño en KB

        return $arrDimensions;
    }
}