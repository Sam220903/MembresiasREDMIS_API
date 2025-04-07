<?php
class TypeCaster {
    public static function castValue($value) {
        if (is_numeric($value)) {
            // Si es un entero (sin decimales)
            if (ctype_digit((string)$value)) {
                return (int)$value;
            } else {
                return (float)$value;
            }
        }
        return $value;
    }
    
    public static function castRow(array $row) {
        foreach ($row as $key => $value) {
            $row[$key] = self::castValue($value);
        }
        return $row;
    }
    
    public static function castRows(array $rows) {
        return array_map([self::class, 'castRow'], $rows);
    }
}
