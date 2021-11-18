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
 * This resource demand tests if a contact resource is currently member of a (static) group
 */
class CRM_Resource_DemandCondition_StaticGroup extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Contact Group (static)");
    }

    /**
     * Get a font-awesome icon for this condition
     */
    public function getIcon()
    {
        return 'fa-users';
    }

    /**
     * Create a new AttributeResourceDemandCondition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param integer $group_id
     *   the ID of the group
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, int $group_id): CRM_Resource_DemandCondition_Attribute
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_StaticGroup',
        ];

        // "pack" the parameters
        $params['parameters'] = [$group_id];

        // we're good, run the creation
        /** @var CRM_Resource_BAO_ResourceDemandCondition $condition_bao */
        $condition_bao = CRM_Resource_BAO_ResourceDemandCondition::create($params);
        return $condition_bao->getImplementation();
    }

    /**
     * Check if the given condition is currently met
     *
     * @param \CRM_Resource_BAO_Resource $resource
     *    the resource to be tested against
     *
     * @param array $error_messages
     *    if there is a problem, add a description to the list of error messages
     *
     * @return boolean does the resource fulfill this condition
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function isFulfilledWithResource($resource, &$error_messages = []) : bool
    {
        // this only works for contacts
        if ($resource->entity_table != 'civicrm_contact') {
            return false;
        }

        list($group_id) = $this->getParametersParsed();
        $group_id = (int) $group_id;
        $group_member = CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(*) 
            FROM civicrm_group_contact
            WHERE group_id = %1
              AND contact_id = %2
              AND status = 'Added'", [
            1 => [$group_id, 'Integer'],
            2 => [$resource->entity_id, 'Integer'],
        ]);

        return $group_member > 0;
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        list($group) = $this->getParametersParsed();
        $groups = \Civi\Api4\Group::get()
            ->addSelect('title')
            ->addWhere('id', '=', (int) $group)
            ->execute();

        return E::ts("Contact is member of group \"%1\"",
            [
                1 => $groups->first()['title']
            ]
        );
    }

    /*****************************************
     ***          FORM INTEGRATION          **
    /****************************************/

    /**
     * Add form fields for the given unavailability
     *
     * @param $form CRM_Core_Form
     *   a form the parameters should be added to
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @param $demand_bao CRM_Resource_BAO_ResourceDemand
     *   the resource demand this condition belongs to
     *
     * @return array
     *    list of field keys (incl. prefix)
     */
    public static function addFormFields($form, $prefix = '', $demand_bao = null)
    {
        // get groups
        $group_list = [];
        $groups = \Civi\Api4\Group::get()
            ->addSelect('title', 'id')
            ->addWhere('saved_search_id', 'IS NULL') // static groups only
            ->execute();
        foreach ($groups as $group) {
            // do something
            $group_list[$group['id']] = $group['title'];
        }

        $form->add(
            'select',
            $prefix . '_group_id',
            E::ts("Group"),
            $group_list,
            true,
            ['class' => 'crm-select2']
        );

        return [
            $prefix . '_group_id',
        ];
    }

    /**
     * Validate our values in the form submission
     *
     * @param $submit_values array
     *   the submitted values
     *
     * @return array
     *    validation errors [field_name => error]
     */
    public static function validateFormSubmission($submit_values, $prefix = '')
    {
        // todo: does there have to be any validation
        return [];
    }

    /**
     * Generate data values
     *
     * @param $data array
     *   form data
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @return array
     *   the data that should be written into the parameters field as a json blob
     */
    public static function compileParameters($data, $prefix = '')
    {
        // format the from/to values properly
        return [
            $data["{$prefix}_group_id"],
        ];
    }

    /**
     * Get the current values for the fields defined in ::addFormFields
     *
     * @param string $prefix
     *   an optional prefix
     *
     * @return array
     *   field-key => current value
     */
    public function getCurrentFormValues($prefix = '')
    {
        $params = $this->getParametersParsed();

        if (isset($params[0])) {
            return [
                "{$prefix}_group_id" => $params[0],
            ];
        } else {
            return [];
        }
    }
}
