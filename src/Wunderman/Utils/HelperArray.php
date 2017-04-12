<?php
namespace Wunderman\Utils;

/**
 * ArrayHelper.
 *
 */
class HelperArray
{

    /**
     * Filtre un tableau à partir de la valeur d'une colonne
     * fonction équivalent de array_filter SUR PHP 5.6  $datasFiltred = array_filter($datas, function($v, $k) use ($activite) {return $v['activite_contentobject_id']  == $activite->getRemoteId(); });
     * 
     */
    public static function array_filter_custom($datas, $filterColName, $filterValue = null)
    {
        $return = array();
        foreach ($datas as $key => $data) {
            if ($data[$filterColName] == $filterValue){
               $return[$key] = $data;
            }
        }

        return $return;

    }
}