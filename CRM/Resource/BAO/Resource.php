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
     * Get the linked entities
     *
     * This is currently based on the resource type specs,
     *  which is an option group with the group_name field containing the table name
     *
     * @return array
     *  table_name => entity_name
     */
    public static function getLinkedEntities() {
        static $linked_entities = null;
        if ($linked_entities === null) {
            $linked_entities = [];
            // use SQL since the API doesn't expose the grouping fields
            $group_data = CRM_Core_DAO::executeQuery("
                SELECT
                 ov.grouping AS entity_table
                FROM civicrm_option_value ov
                INNER JOIN civicrm_option_group og
                       ON ov.option_group_id = og.id
                       AND og.name = 'resource_types'
                ")->fetchAll();
            foreach ($group_data as $group_datum) {
                $linked_entities[$group_datum['entity_table']] =
                    CRM_Core_DAO_AllCoreTables::getEntityNameForTable($group_datum['entity_table']);
            }
        }
        return $linked_entities;
    }

    /**
     * Used as in the entityTypes hook als follows:
     *  'links_callback' => ['CRM_Resource_BAO_Resource::add_resource_links']
     *
     * @note current not active
     *
     * @param string $class
     * @param array $links
     */
    public static function add_resource_links($class, &$links)
    {
        $links[] = new CRM_Core_Reference_Dynamic(self::getTableName(), 'entity_id', NULL, 'id', 'entity_table');
    }

    /** TEMPLATE: COPY THIS TO CRM_Resource_DAO_Resource AFTER REBUILDING THE DAOs! */
    public static function buildOptions($fieldName, $context = NULL, $props = []) {
        if ($fieldName == 'entity_table') {
            return CRM_Resource_BAO_Resource::getLinkedEntities();
        } else {
            return parent::buildOptions($fieldName, $context, $props);
        }
    }

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
