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
    public static function addContactResourceTab(&$tabs, $context)
    {
        // add a resource tab to the summary view
        $resource = \Civi\Api4\Resource::get()
            ->addWhere('entity_table', '=', 'civicrm_contact')
            ->addWhere('entity_id', '=', $context['contact_id'])
            ->execute()
            ->first();
        if (!empty($resource['id'])) { // contact already is a resource:
            // get the assignment count
            $assignment_count = \Civi\Api4\ResourceAssignment::get()
                ->addWhere('resource_id', '=', $resource['id'])
                ->selectRowCount()
                ->execute();

            // generate tab
            $tabs['resource'] = [
                'id'      => 'resource',
                'title'   => E::ts("Assignments"),
                'url'     => CRM_Utils_System::url(
                    'civicrm/resource/view',
                    "id={$resource['id']}"
                ),
                'count'   => $assignment_count->rowCount, // todo: only active/future assignments
                'valid'   => 1,
                'icon' => "crm-i fa-user-md", // todo: use resource type icon
                'active'  => 1,
                'current' => false,
            ];

            // add our tab's JS file
            Civi::resources()->addScriptUrl(E::url('js/contact_view.js'));

        } else { // contact is not a resource: offer to become one (if applicable)

            // first check, if there even is an (active) resource type for contacts
            $contact_resource_types = CRM_Resource_Types::getForEntityTable('civicrm_contact');
            if (!empty($contact_resource_types)) {
                // contact isn't a resource -> offer to become one
                $tabs['resource'] = [
                    'id'      => 'resource',
                    'title'   => E::ts("Assignments"),
                    'url'     => CRM_Utils_System::url(
                        'civicrm/resource/create',
                        "entity_id={$context['contact_id']}&entity_table=civicrm_contact"
                    ),
                    'icon' => "crm-i fa-question",
                    'count'   => 0,
                    'valid'   => 0,
                    'active'  => 0,
                    'current' => false,
                ];
            }
        }
    }
}
