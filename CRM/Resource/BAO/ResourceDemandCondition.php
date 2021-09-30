<?php
/*-------------------------------------------------------+
| SYSTOPIA Resource Framework                            |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Resource_ExtensionUtil as E;

class CRM_Resource_BAO_ResourceDemandCondition extends CRM_Resource_DAO_ResourceDemandCondition
{

    /**
     * Create a new ResourceDemandCondition based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_ResourceDemandCondition|NULL
     *
     * public static function create($params) {
     * $className = 'CRM_Resource_DAO_ResourceDemandCondition';
     * $entityName = 'ResourceDemandCondition';
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
