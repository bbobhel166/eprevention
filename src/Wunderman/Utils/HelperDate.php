<?php
namespace Wunderman\Utils;

/**
 * DateHelper.
 *
 */
class HelperDate
{
    /**
     * Convertion d'une date string en dateTime
     * 
     * @param  string $date   
     * @param  array $inputformats formats en entrée, plusieurs masques de format autorisé array('d-m-Y', 'd/m/Y', 'dmY')
     * @param  array $outputformat format
     * @return dateTime or false       
     */
    public static function dateConvertMultipleFormat($date, $inputformats = array('Y-m-d H:i:s'), $outputformat = 'Y-m-d')
    {
        $isValid = HelperDate::dateIsValid($date, $inputformats);
        if(!$isValid){
           return false;     
        }

        foreach ($inputformats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if($d && $d->format($format) == $date){
                return $d;
            }
        }

        return false;
    }

    /**
     * Date is valide
     * 
     * @param  string $date   [description]
     * @param  array $formats plusieurs masques de format autorisé array('d-m-Y', 'd/m/Y', 'dmY')
     * @return boolean        [description]
     */
    public static function dateIsValid($date, $formats = array('Y-m-d H:i:s'))
    {
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if($d && $d->format($format) == $date){
                return true;
            }
        }

        return false;
    }


    /**
     * Date is valide or null
     * 
     * @param  string $date   [description]
     * @param  array $format plusieurs masques de format autorisé array('d-m-Y', 'd/m/Y', 'dmY')
     * @return boolean         [description]
     */
    public static function dateIsValidOrNull($date, $formats = array('Y-m-d H:i:s'))
    {
        if (null == $date){
            return true;
        }

        return HelperDate::dateIsValid($date, $formats);
    }
}