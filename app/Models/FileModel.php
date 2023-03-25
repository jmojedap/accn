<?php namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table      = 'files';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'file_name', 'ext', 'folder', 'is_image', 'type', 'type_id','title', 'subtitle',
        'description', 'keywords', 'external_link',
        'size', 'width', 'height',
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
     * Segmento SQL SELECT para construir consulta
     * 2020-08-11
     */
    function select($format = 'default')
    {
        $arrSelect['default'] = 'id, file_name, title, url, url_thumbnail, folder';
        $arrSelect['basic'] = 'id, title, url, url_thumbnail';
        $arrSelect['admin'] = '*';

        return $arrSelect[$format];
    }

    public function search($input)
    {
        $qFields = ['file_name', 'title', 'subtitle', 'description'];
        $filtersNames = ['q','table_id__eq'];
        
        $search = new \App\Libraries\Search();
        $filters = $search->filters($input, $filtersNames);
        $settings = $search->settings($input);
        $searchCondition = $search->condition($input, $qFields);

        $dbTools = new \App\Models\DbTools();
        $qtyResults = \App\Models\DbTools::numRows('files', $searchCondition);

        $data['settings'] = $settings;
        $data['filters'] = $filters;
        $data['results'] = $this->searchResults($searchCondition, $settings);
        $data['qtyResults'] = $qtyResults;
        $data['maxPage'] = ($qtyResults > 0) ? ceil($qtyResults / $settings['perPage']) : 1;

        return $data;
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
     */
    public function get($fileId, $selectFormat = 'default')
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
        $data['headTitle'] = $row->title;

        return $data;
    }

// UPLOAD
//-----------------------------------------------------------------------------

    /**
     * Guarda el archivo en carpeta de UPLOADS y devuelve array para creación de registro en la tabla files
     * 2021-01-25
     */
    public function upload($request, $userId)
    {
        helper('text'); //Para random_string

        $file = $request->getFile('file_field');
        $aRow = $request->getPost();

        //Construir registro
        $aRow['title'] = substr($file->getName(),0, strlen($file->getName()) - (strlen($file->getExtension()) + 1));
        $aRow['ext'] = $file->getExtension();
        $aRow['folder'] = date('Y/m/');
        $aRow['is_image'] = ( substr($file->getMimeType(),0,5) == 'image' ) ? 1 : 0 ;
        $aRow['type'] = $file->getMimeType();
        $aRow['file_name'] =  $userId . '_' .  date('YmdHis') . random_string('numeric', 3) . '.' . $file->getExtension();
        $aRow['size'] = intval($file->getSize()/1028);
        $aRow['url'] = URL_UPLOADS . $aRow['folder'] . $aRow['file_name'];
        $aRow['url_thumbnail'] = URL_UPLOADS . $aRow['folder'] . 'sm_' . $aRow['file_name'];
        $aRow['creator_id'] = $userId;
        $aRow['updater_id'] = $userId;
        $aRow['created_at'] = date('Y-m-d H:i:s');
        $aRow['updated_at'] = date('Y-m-d H:i:s');

        //Guardar archivo en carpeta
        if (! $file->hasMoved()) {
            $file->move(PATH_UPLOADS . $aRow['folder'], $aRow['file_name']);
        }

        return $aRow;
    }

    /**
     * Eliminar archivo y miniaturas si existen
     * 2021-01-25
     */
    public function unlink($fileId)
    {
        $row = $this->row($fileId);

        //Array rutas de archivos
        $paths[] = PATH_UPLOADS . $row->folder . 'sm_' . $row->file_name;   //Thumbnail
        $paths[] = PATH_UPLOADS . $row->folder . $row->file_name;           //Archivo original

        //Eliminar archivos
        foreach ($paths as $path)
        {
            if (  file_exists($path) ) unlink($path);
        }
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
            //->fit($pixels, null, 'center')   //Miniatura cuadrada
            ->resize($width, $height) //Miniatura no cuadrada
            ->save($thumbnail_path, 80);

        return $thumbnail_path;
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