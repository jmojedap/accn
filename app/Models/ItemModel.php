<?php namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table      = 'items';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'category_id', 'code', 'name', 'description', 'abbreviation',
        'position', 'slug', 'filters', 'long_name', 'short_name', 'label_class'
    ];

    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    protected $validationRules    = [
        'category_id' => 'required',
        'code' => 'required',
        'name' => 'required',
    ];
    protected $validationMessages = [
        'category_id' => [
            'required' => 'La categoría del ítem es obligatoria',
        ]
    ];
    protected $skipValidation     = false;


// Buscador
//-----------------------------------------------------------------------------

    /**
     * Segmento SQL SELECT para construir consulta
     * 2020-08-11
     */
    function select($format = 'default')
    {
        $arrSelect['default'] = 'id, category_id, code, name';
        $arrSelect['basic'] = 'id, code, name';
        $arrSelect['options'] = 'code, name, slug';
        $arrSelect['optionsAbbreviation'] = 'code, name, abbreviation';
        $arrSelect['admin'] = '*';

        return $arrSelect[$format];
    }

    /**
     * Devuelve listado de itemss filtrados o segmentaos por criterios en $input
     * @param array $input
     * @return array $data
     * 2024-05-23
     */
    public function search($input)
    {
        $qFields = ['name', 'description', ];
        $filtersNames = ['q','category_id__eq', 'code__eq'];
        
        $search = new \App\Libraries\Search();
        $filters = $search->filters($input, $filtersNames);
        $settings = $search->settings($input);
        $searchCondition = $search->condition($input, $qFields);

        $dbTools = new \App\Models\DbTools();
        $qtyResults = \App\Models\DbTools::numRows('items', $searchCondition);

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

// Datos
//-----------------------------------------------------------------------------

    /**
     * Row de un item
     * 2023-04-30
     */
    public function getRow($condition, $selectFormat = 'default')
    {
        $row = NULL;
        $conditionChecked = 'id = 0';
        if ( strlen($condition) > 0 ) { $conditionChecked = $condition; }

        $builder = $this->builder();
        $builder->select($this->select($selectFormat));
        $builder->where($conditionChecked);
        $query = $builder->get();

        if ( $query->getRow(0) ) { $row = $query->getRow(0); }

        return $row;
    }

    public function basic($row)
    {
        $data['row'] = $row;
        $data['headTitle'] = $row->name;

        return $data;
    }

    /**
     * Convierte el array de input de un formulario (POST) en un array para
     * crear o actualizar un registro en la tabla items
     * 2024-05-24
     */
    public function inputToRow($input)
    {
        $aRow = $input;
        return $aRow;
    }

// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Elimina registro tabla items, que cumplen con una condición SQL
     * 2024-05-29
     * @param string $condition :: condición sql de registros a eliminar
     * @return $result :: resultado de la eliminación
     */
    public function deleteByCondition($condition = 'id = 0')
    {
        $restriction = $this->deleteRestriction($condition);

        if ( strlen($restriction) == 0 ) {
            //No hay restricción, eliminar
            $result = $this->where($condition)->delete();
        } else {
            //Devolver texto de restricción
            $result = $restriction;
        }

        return $result;
    }

    /**
     * Devuelve restricción, si existe alguna para eliminar un ítem
     * Si no hay restricción devuelve cadena vacía, se puede eliminar ítem.
     * 2024-05-29
     * @param string $condition :: Condición SQL para evalua restricción
     * @return string $restriction
     */
    public function deleteRestriction($condition)
    {
        $restriction = '';

        return trim($restriction);
    }

// INFO
//-----------------------------------------------------------------------------

    public function arrOptions($condition, $selectFormat = 'options')
    {
        $builder = $this->builder();

        $builder->select($this->select($selectFormat));
        $builder->where($condition);
        $builder->orderBy('position', 'ASC');
        $builder->orderBy('code', 'ASC');
        $query = $builder->get();

        return $query->getResultArray();
    }
}