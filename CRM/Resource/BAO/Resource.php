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

class CRM_Resource_BAO_Resource extends CRM_Resource_DAO_Resource
{

    /**
     * Check if the resource is available, judging by
     *  - the attached availabilities
     *  - current assignments
     *
     * @see CRM_Resource_BAO_Resource::isAvailableSqlClause
     *
     * @param null $from_timestamp
     * @param null $to_timestamp
     *
     * @return boolean true iff the resource is available (wrt to the time parameters)
     */
    public function isAvailable($from_timestamp = null, $to_timestamp = null)
    {
        return CRM_Resource_BAO_ResourceUnavailability::isResourceAvailable($this->id, $from_timestamp, $to_timestamp);
    }


    /**
     * Create a new Resource based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_Resource|NULL
     *
     * public static function create($params) {
     * $className = 'CRM_Resource_DAO_Resource';
     * $entityName = 'Resource';
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
