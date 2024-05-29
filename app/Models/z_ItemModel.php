<?php namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table      = 'items';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['id', 'name'];

    protected $useTimestamps = true;
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    protected $validationRules    = [];
    protected $validationMessages = [
        'name' => [
            'required' => 'El nombre del item es obligatorio'
        ]
    ];
    protected $skipValidation     = false;

// 
//-----------------------------------------------------------------------------

    /**
     * Segmento SQL SELECT para construir consulta
     * 2023-02-22
     */
    function select($format = 'basic')
    {
        $arrSelect['basic'] = 'id, name, code';
        $arrSelect['options'] = 'code, name';
        $arrSelect['optionsFull'] = 'code, name, short_name,
            abbreviation, slug, parent_id';
        $arrSelect['optionsAbbreviation'] = 'code, abbreviation';
        return $arrSelect[$format];
    }

    function list($filters, $num_page, $per_page)
    {
        $builder = $this->builder();
        
        $builder->select($this->select());
        
        $condition = $this->search_condition($filters);
        if ( $condition ) $builder->where($condition);
        
        //Paginación
        $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado
        $builder->limit($per_page, $offset);

        //Orden
        $order_by = ( array_key_exists('o', $filters) ) ? $filters['o'] : 'code';
        $order_type = ( array_key_exists('ot', $filters) ) ? $filters['ot'] : 'ASC';
        $builder->orderBy($order_by, $order_type);

        $query = $builder->get();

        return $query->getResult();
    }

    /**
     * String con condición WHERE SQL para filtrar user
     */
    public function search_condition($filters)
    {
        //$filters = $search->filters();
        $condition = NULL;

        //q words condition
        if ( array_key_exists('q', $filters) )
        {
            $search = new \App\Libraries\Search(); 
            
            $words_condition = $search->words_condition($filters['q'], array('item', 'description'));
            if ( $words_condition )
            {
                $condition .= $words_condition . ' AND ';
            }
        }

        if ( array_key_exists('cat', $filters) ) $condition .= "category_id = {$filters['cat']} AND ";

        if ( strlen($condition) > 0 ) $condition = substr($condition, 0, -5);

        return $condition;
    }

// Otras
//-----------------------------------------------------------------------------

    public function array_by_condition($condition = 'category_id = 0', $field = 'name')
    {
        $builder = $this->builder();
        $builder->select($this->select());
        $builder->where($condition);
        $query = $builder->get();

        $pml = new \App\Libraries\Pml();

        $arr_items = $pml->query_to_options($query, $field, 'code', 'Roles de usuario');

        return $arr_items;
    }

    public function arrOptions($condition, $selectFormat = 'options')
    {
        $builder = $this->builder();

        $builder->select($this->select($selectFormat));
        $builder->where($condition);
        $query = $builder->get();

        return $query->getResultArray();
    }
}