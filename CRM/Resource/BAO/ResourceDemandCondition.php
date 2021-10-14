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
     * Return an object of the specific class, i.e. the object that matches
     *   the provided class
     *
     * @return CRM_Resource_BAO_ResourceDemandCondition subclass
     */
    public function getImplementation()
    {
        $implementation = new $this->class_name();
        $implementation->setFrom($this);
        return $implementation;
    }

    /**
     * Create a new ResourceDemandCondition based on array-data
     *
     * @param array $params key-value pairs
     *
     * @return CRM_Resource_BAO_ResourceDemandCondition|NULL
     */
    public static function create($params)
    {
        // run pre hook
        $className = 'CRM_Resource_BAO_ResourceDemandCondition';
        $entityName = 'ResourceDemandCondition';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

        // create entity
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();

        // run post hook
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        return $instance;
    }

    /**
     * Check if the given condition is currently met
     *
     * @param \CRM_Resource_BAO_Resource $resource
     *
     * @return boolean does the resource fulfill this condition
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function isFulfilledWithResource($resource)
    {
        return false;
    }

    /**
     * Get a parsed version of the stored parameters
     *
     * @return array|null
     */
    public function getParameters()
    {
        return json_decode($this->parameters);
    }
}
