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
 * CiviResource UI integration functionality
 */
class CRM_Resource_UI
{
    /**
     * Inject contact resource tab
     *
     * @param array $tabs
     *    civicrm_tabset structure
     * @param array $context
     *    context information
     */
    public static function addResourceTab(&$tabs, $tabsetName, $context)
    {
        switch ($tabsetName) {
            case 'civicrm/contact/view':
                $entity_table = 'civicrm_contact';
                $entity_id = $context['contact_id'];
                break;
            case 'civicrm/eck/entity':
                $entity_table = $context['entity_type']['table_name'];
                $entity_id = $context['entity_id'];
                break;
        }
        // add a resource tab to the summary view
        $resource = CRM_Resource_BAO_Resource::getResource($entity_id, $entity_table);
        if ($resource) {
            // Entity already is a resource:
            // get the assignment count
            $assignment_count = \Civi\Api4\ResourceAssignment::get()
                ->addWhere('resource_id', '=', $resource->id)
                ->selectRowCount()
                ->execute();

            // generate tab
            $tabs['resource'] = [
                'id'      => 'resource',
                'title'   => E::ts("Resource Assignments"),
                'url'     => CRM_Utils_System::url(
                    'civicrm/resource/view',
                    "id={$resource->id}"
                ),
                'count'   => $assignment_count->rowCount, // todo: only active/future assignments
                'valid'   => 1,
                'icon' => "crm-i fa-user-md", // todo: use resource type icon
                'active'  => 1,
                'current' => false,
            ];

            // add our tab's JS file
            Civi::resources()->addScriptFile(E::LONG_NAME, 'js/contact_view.js', 10, 'page-header');

        } else {
            // Entity is not a resource: offer to become one (if applicable)

            // first check, if there even is an (active) resource type for contacts
            $entity_resource_types = CRM_Resource_Types::getForEntityTable($entity_table);
            if (!empty($entity_resource_types)) {
                // contact isn't a resource -> offer to become one
                $tabs['resource'] = [
                    'id'      => 'resource',
                    'title'   => E::ts("Resource Assignments"),
                    'url'     => CRM_Utils_System::url(
                        'civicrm/resource/create',
                        "entity_id={$entity_id}&entity_table={$entity_table}"
                    ),
                    'icon' => "crm-i fa-question",
                    'count'   => 0,
                    'valid'   => 0,
                    'active'  => 1,
                    'current' => false,
                    'class' => 'ajaxForm',
                ];
            }
        }
    }

    /**
     * Inject event resource demand tab
     *
     * @param array $tabs
     *    civicrm_tabset structure
     * @param array $context
     *    context information
     */
    public static function addEventResourceDemandTab(&$tabs, $context)
    {
        // todo: add setting to enable event demands?

        // add required resources tab
        if (empty($context['event_id'])) {
            $tabs['resourcedemands'] = [
                'title'   => E::ts("Required Resources"),
                'url'     => 'civicrm/event/manage/resourcedemands',
                'field'   => 'id',
            ];
        } else {
            $tabs['resourcedemands'] = [
                'title'   => E::ts("Required Resources"),
                'link'    => CRM_Utils_System::url(
                    'civicrm/event/manage/resourcedemands',
                    "action=update&reset=1&id={$context['event_id']}"
                ),
                'valid'   => 1,
                'active'  => 1,
                'current' => false,
            ];
        }
    }
}
