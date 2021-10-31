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

/**
 * CiviResource types functions based on the 'resource_types' option group
 */
class CRM_Resource_Types
{
    private static $types = null;

    /**
     * Get the list of all resource types
     *
     * @return array
     *   a list of resource types with the following attributes
     */
    public static function getAll()
    {
        if (self::$types === null) {
            self::$types = [];

            // use a sql query to load the types, since the grouping isn't exposed in the api
            $group_data = CRM_Core_DAO::executeQuery(
                "
                SELECT
                    ov.label     AS label,
                    ov.value     AS value, 
                    ov.icon      AS icon, 
                    ov.grouping  AS entity_table,
                    ov.is_active AS is_active
                FROM civicrm_option_value ov
                INNER JOIN civicrm_option_group og
                       ON ov.option_group_id = og.id
                       AND og.name = 'resource_types'
                WHERE ov.is_active = 1
                ORDER BY weight ASC;"
            )->fetchAll();
            foreach ($group_data as $group_datum) {
                self::$types[$group_datum['value']] = [
                    'id'           => $group_datum['value'],
                    'label'        => $group_datum['label'],
                    'icon'         => $group_datum['icon'],
                    'entity_table' => $group_datum['entity_table'],
                    'is_active'    => $group_datum['is_active'],
                ];
            }
        }
        return self::$types;
    }

    /**
     * Flush the internal cache of resource types.
     * This needs to be done after changes to the resource type option group
     */
    public static function clearCache()
    {
        self::$types = null;
    }

    /**
     * Get Resource-Type by ID
     *
     * @param integer $id
     *   the type ID
     *
     * @return array|null
     *   type data if exists
     */
    public static function getType($id)
    {
        $all_types = self::getAll();
        return $all_types[$id] ?? null;
    }

    /**
     * Check if there are resource types available for the given civicrm table name
     *
     * @param string $table_name
     *   the table name, e.g. civicrm_contact
     *
     * @param boolean $active_only
     *    return only active types
     *
     * @return array
     *   list of types that use this entity table
     */
    public static function getForEntityTable($table_name, $active_only = true)
    {
        $types = [];
        $all_types = self::getAll();
        foreach ($all_types as $type) {
            if ($type['entity_table'] == $table_name) {
                if ($active_only && empty($type['is_active'])) continue;
                $types[$type['id']] = $type;
            }
        }
        return $types;
    }

    /**
     * Get the entity name by the given table
     *
     * @param string $entity_table
     */
    public static function getEntityName($entity_table)
    {
        // todo: l10n? eck?
        return CRM_Core_DAO_AllCoreTables::getEntityNameForTable($entity_table);
    }
}
