<?php

namespace App\Libraries;

class DbUtils
{
    /**
     * Devuelve un registro por ID.
     */
    public static function rowId(string $table, int $id, string $select = '*')
    {
        return db_connect()->table($table)
            ->select($select)
            ->where('id', $id)
            ->get()
            ->getRow(0);
    }

    /**
     * Devuelve un registro según condición.
     */
    public static function row(string $table, string $condition = 'id = 0', string $select = '*')
    {
        return db_connect()->table($table)
            ->select($select)
            ->where($condition)
            ->get()
            ->getRow(0);
    }

    /**
     * Devuelve la cantidad de registros que cumplen la condición.
     */
    public static function numRows(string $table, string $condition): int
    {
        return db_connect()->table($table)->where($condition)->countAllResults();
    }

    /**
     * Inserta o actualiza un registro en una tabla.
     */
    public static function saveRow(string $table, string $condition, array $aRow): ?int
    {
        $row = self::row($table, $condition);
        $db = db_connect();
        $builder = $db->table($table);

        if (is_null($row)) {
            $builder->insert($aRow);
            return $db->insertID();
        } else {
            $builder->where('id', $row->id)->update($aRow);
            return $row->id;
        }
    }

    /**
     * Verifica si un valor es único en un campo de una tabla.
     */
    public static function isUnique(string $table, string $field, $value, $rowId = null): int
    {
        $isUnique = 1;    //Valor por defecto
        $builder = db_connect()->table($table)->where($field, $value);
        if (!is_null($rowId)) {
            $builder->where('id !=', $rowId);
        }
        //Verificar cantidad de resultados
        if ( $builder->countAllResults() > 0 ) { $isUnique = 0; }

        return $isUnique;
    }

    /**
     * Actualiza idcode basado en el ID y parte aleatoria.
     */
    public static function setIdCode(string $table, int $rowId): string
    {
        helper('text');
        $idCode = $rowId . random_string('numeric', 4);
        self::saveRow($table, "id = {$rowId}", ['idcode' => $idCode]);
        return $idCode;
    }

    /**
     * Genera un slug a partir de un texto.
     */
    public static function slug(string $text): string
    {
        helper('text');
        $slug = convert_accented_characters($text);
        $slug = url_title($slug, '-', true);
        return substr($slug, 0, 140);
    }

    /**
     * Genera un slug único en una tabla.
     */
    public static function uniqueSlug(string $text, string $table, string $field = 'slug'): string
    {
        $slug = self::slug($text);
        $numRows = self::numRows($table, "{$field} = '{$slug}'");
        return $slug . ($numRows > 0 ? '-' . random_string('numeric', 3) : '');
    }
}
