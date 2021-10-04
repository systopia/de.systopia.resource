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

class CRM_Resource_BAO_ResourceUnavailability extends CRM_Resource_DAO_ResourceUnavailability
{
    /** @var CRM_Resource_BAO_ResourceUnavailability */
    private $implementation = null;

    /**
     * Return an object of the specific class, i.e. the object that matches
     *   the provided class
     *
     * @return CRM_Resource_BAO_ResourceUnavailability subclass
     */
    public function getImplementation()
    {
        if (!isset($this->implementation)) {
            $this->implementation = new $this->class_name();
            $this->implementation->setFrom($this);
        }
        return $this->implementation;
    }

    /**
     * Check whether the given resource is available (in the given time frame)
     *
     * @param integer $resource_id
     * @param string $from_timestamp
     * @param string $to_timestamp
     */
    public static function isResourceAvailable($resource_id, $from_timestamp = null, $to_timestamp = null)
    {
        // load all availabilities restrictions
        $unavailability_search = new CRM_Resource_BAO_ResourceUnavailability();
        $unavailability_search->resource_id = (int) $resource_id;
        $unavailability_search->find();
        while ($unavailability_search->fetch()) {
            $implementation = $unavailability_search->getImplementation();
            if ($implementation->isActive($from_timestamp, $to_timestamp)) {
                return false;
            }
        }

        // todo: check if currently assigned

        // no problems found
        return true;
    }

    /**
     * Check if the
     * @param null $from_timestamp
     * @param null $to_timestamp
     *
     * @return false
     */
    public function isActive($from_timestamp = null, $to_timestamp = null)
    {
        // this should really be overwritten
        Civi::log()->warning("CRM_Resource_BAO_ResourceUnavailability::isActive called, this should be overwritten");
        return true;
    }

    /**
     * Create a new ResourceUnavailability based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_DAO_ResourceUnavailability|NULL
     *
     * public static function create($params) {
     * $className = 'CRM_Resource_DAO_ResourceUnavailability';
     * $entityName = 'ResourceUnavailability';
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
