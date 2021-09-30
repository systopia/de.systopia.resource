<?php

use CRM_Resource_ExtensionUtil as E;

class CRM_Resource_BAO_ResourceDemand extends CRM_Resource_DAO_ResourceDemand
{

    /**
     * Create a new ResourceDemand based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_ResourceDemand|NULL
     *
     * public static function create($params) {
     * $className = 'CRM_Resource_DAO_ResourceDemand';
     * $entityName = 'ResourceDemand';
     * $hook = empty($params['id']) ? 'create' : 'edit';
     *
     * CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
     * $instance = new $className();
     * $instance->copyValues($params);
     * $instance->save();
     * CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
     *
     * return $instance;
     * } */

}
