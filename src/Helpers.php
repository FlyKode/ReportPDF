<?php

namespace Flykode\ReportPDF;

class Helpers {
    /**
     * Column Types
     */
    const TEXT_COLUMN = 'text';
    const FLOAT_COLUMN = 'float';
    const NUMBER_COLUMN = 'number';
    const INTEGER_COLUMN = 'integer';
    const CURRENCY_COLUMN = 'currency';
    const DATE_COLUMN = 'date';
    const CENTERTEXT_COLUMN = 'text';
    const RIGHTTEXT_COLUMN = 'text';
    const R_TEXT_COLUMN = 'rtext';
    
    /**
     * Function Types
     */
    const SUM_FUNCTION = 'sum';
    const AVG_FUNCTION = 'avg';
    const COUNT_FUNCTION = 'count';
    const MAX_FUNCTION = 'max';
    const MIN_FUNCTION = 'min';
    const ACCUMULATE_FUNCTION = 'accumulate';
    const VALUE_FUNCTION = 'value';

    public static function formatByType($type,$value,$currencySymbol='R$')
    {
        switch ($type) {
            case self::FLOAT_COLUMN:
                $value = number_format($value, 2, ',', '.');
                break;
    
            case self::NUMBER_COLUMN:
                $value = number_format($value, 3, ',', '.');
                break;
    
            case self::CURRENCY_COLUMN:
                $value = $currencySymbol.' '.number_format($value, 2, ',', '.');
                break;
    
            case self::DATE_COLUMN:// falta implementar
                $value = $value;
                break;
        }
    
        return $value;
    }
}