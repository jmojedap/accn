/**
 * FUNCIONES GENERALES PARA EL USO EN EL LENGUAJE JAVASCRIPT
 * DESARROLLADAS POR Mauricio Ojeda-Pepinosa
 * 2014-08-14
 * 
 */

var Pcrn = new function()
{
    /**
     * Controlar el value de una variable numérica para que su value permanezca
     * en un rango determinado
     * 
     * @param {type} $value
     * @param {type} $min
     * @param {type} $max
     * @returns {unresolved}
     */
    this.limit_between = function($value, $min, $max)
    {
        $limited_value = $value;

        if ( $limited_value < $min ) $limited_value = $min;
        if ( $limited_value > $max ) $limited_value = $max;

        return $limited_value;
    };
    
    /**
     * Controlar el value de una variable numérica para que su value permanezca
     * en un rango determinado,
     * Si supera el máximo devuelve el mínimo
     * Si supera el mínimo devuelve el máximo
     * 
     * @param {type} $value
     * @param {type} $min
     * @param {type} $max
     * @returns {unresolved}
     */
    this.cycle_between = function($value, $min, $max)
    {
        $limited_value = $value;

        if ( $limited_value < $min ) $limited_value = $max;
        if ( $limited_value > $max ) $limited_value = $min;

        return $limited_value;
    };
};