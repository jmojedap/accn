<?php namespace App\Models;

use CodeIgniter\Model;

class DbTools extends Model
{

// Rows Registros
//-----------------------------------------------------------------------------

    /**
     * Devuelve row de una $table table.id determinado
     * 2023-01-29
     */
    public static function rowId($table, $id, $select = '*')
    {
        $db = db_connect();
        $query = $db->table($table)
            ->select($select)
            ->where('id', $id)->get();
        return $query->getRow(0);
    }

    /**
     * Devuelve row de una $table table.id determinado
     * 2023-01-29
     */
    public static function row($table, $condition = 'id = 0', $select = '*')
    {
        $checkedCondition = ( ! is_null($condition)) ? $condition : 'id = 0' ;
        $db = db_connect();
        $query = $db->table($table)
            ->select($select)
            ->where($checkedCondition)->get();
        return $query->getRow(0);
    }

    public static function numRows($table, $condition)
    {
        $db = db_connect();
        $numRows = $db->table($table)->where($condition)->countAllResults();
        return $numRows;
    }


    /**
     * Devuelve row de una $table table.id determinado
     * 2020-08-07
     */
    public static function saveRow($table, $condition, $aRow)
    {
        $savedId = NULL;    //Valor por defecto

        $row = DbTools::row($table, $condition);

        $db = db_connect();
        $builder = $db->table($table);

        if ( is_null($row) )
        {
            $builder->insert($aRow);
            $savedId = $db->insertID();
        } else {
            $builder->where('id', $row->id);
            $builder->update($aRow);
            $savedId = $row->id;
        }

        return $savedId;
    }

    /**
     * Determina si un valor para un field es único en la table. Si se agrega
     * el ID de un row específico, lo descarta en la búsqueda, valor ya existente
     * 2022-05-07
     */
    public static function isUnique($table, $field, $value, $rowId = NULL)
    {
        $isUnique = 1;    //Valor por defecto

        //Construir consulta
        $db = db_connect();
        $builder = $db->table($table);
        $builder->where($field, $value);
        if ( ! is_null($rowId) ) { $builder->where("id <> {$rowId}"); }

        //Verificar cantidad de resultados
        if ( $builder->countAllResults() > 0 ) { $isUnique = 0; }

        return $isUnique;
    }

    /**
     * Actualiza el campo {$table].idcode según el $rowId y una parte aleatoria
     * 2023-05-07
     */
    public static function setIdCode($table, $rowId)
    {
        helper('text');
        $aRow['idcode'] = $rowId . random_string('numeric', 4);

        DbTools::saveRow($table, "id = {$rowId}", $aRow);

        return $aRow['idcode'];
    }

    public static function slug($text)
    {
        helper('text');
        $slug = convert_accented_characters($text);     //Without accents
        $slug = url_title($slug, '-', TRUE);            //Without spaces Sin espaciosy sin caracteres
        $slug = substr($slug, 0, 140);
        
        return $slug;
    }
    
    public static function uniqueSlug($text, $table, $field = 'slug')
    {
        $baseSlug = DbTools::slug($text);
        
        //Count equal slug
            $condition = "{$field} = '{$baseSlug}'";
            $numRows = DbTools::numRows($table, $condition);
        
        $sufix = '';
        if ( $numRows > 0 )
        {
            helper('text');
            $sufix = '-' . random_string('numeric', 3);
        }
        
        $slug = $baseSlug . $sufix;
        
        return $slug;
    }
}