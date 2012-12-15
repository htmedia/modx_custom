//<?php
/**
 * resField
 * 
 * Return field content
 *
 * @category snippet
 * @version 1.0
 * @internal @modx_category Content
 */

/*
* resField
* return field content
*
* @id - resource id
* @field - field name
*
* [!resField? &id=`1` &field=`longtitle`!]
*/
    
$id = isset($id) ? intval($id) : 1;
$field = isset($field) ? trim($field) : 'pagetitle';

$info = $modx->getTemplateVar($field, '*', $id, 0);
$result = $info['value'];

return $result;