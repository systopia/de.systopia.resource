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


class CRM_Resource_Form_Task_CreateContactResource extends CRM_Contact_Form_Task
{
    public function buildQuickForm()
    {
        parent::buildQuickForm();

        // calculate how many of those are already resources
        $contact_count = count($this->_contactIds);
        $contacts_without_resources = $this->getContactNoResourceIds();
        $contact_with_resource_count = $contact_count - count($contacts_without_resources);
        $this->assign('contact_with_resource_count', $contact_with_resource_count);

        // get the resource types
        $resource_type_selection = [];
        $resource_types = CRM_Resource_Types::getForEntityTable('civicrm_contact');
        foreach ($resource_types as $resource_type) {
            $resource_type_selection[$resource_type['id']] = $resource_type['label'];
        }


        $this->setTitle(E::ts("Use %1 Contacts as Resources", [1 => count($contacts_without_resources)]));

        $this->add(
            'select',
            'resource_type_id',
            E::ts('Resource Type'),
            $resource_type_selection,
            true,
            [
                'class' => 'crm-select2',
            ]
        );

        $this->add(
            'select',
            'label_pattern',
            E::ts('Resource Label'),
            [
                'display_name' => E::ts("Display Name"),
                // todo: add more? if so, also add below in getSelectClause
            ],
            true,
            [
                'class' => 'crm-select2',
            ]
        );
    }

    public function postProcess()
    {
        $config = $this->exportValues(null, true);
        $contacts_without_resources = $this->getContactNoResourceIds();
        $counter = 0;

        if (!empty($contacts_without_resources)) {
            // load required data
            $select_clause = $this->getSelectClause($config);
            $contact_search = CRM_Core_DAO::executeQuery("
            SELECT contact.id       AS contact_id,
                   {$select_clause} AS resource_label
            FROM civicrm_contact contact
            WHERE contact.id IN (%1)",
                                                         [1 => [implode(',', $contacts_without_resources), 'CommaSeparatedIntegers']]);

            // create the resources
            while ($contact_search->fetch()) {
                // todo: error handling?
                civicrm_api3('Resource', 'create', [
                    'entity_id' => $contact_search->contact_id,
                    'entity_table' => 'civicrm_contact',
                    'resource_type_id' => $config['resource_type_id'],
                    'label' => $contact_search->resource_label,
                ]);
                $counter++;
            }
        }


        CRM_Core_Session::setStatus(
            E::ts("%1 contacts have been made available as a resource.", [1 => $counter]),
            E::ts("Resources Created"),
            'info'
        );

        parent::postProcess();
    }

    /**
     * Filter the list of contact IDs to exclude the ones that already have a resource
     */
    private function getContactNoResourceIds(): array
    {
        $contact_ids = [];
        $contact_search = CRM_Core_DAO::executeQuery("
            SELECT contact.id AS contact_id
            FROM civicrm_contact contact
            LEFT JOIN civicrm_resource resource
                   ON resource.entity_id = contact.id
                   AND resource.entity_table = 'civicrm_contact' 
            WHERE contact.id IN (%1)
              AND contact.is_deleted = 0
              AND resource.id IS NULL",
            [1 => [implode(',', $this->_contactIds), 'CommaSeparatedIntegers']]);
        while ($contact_search->fetch()) {
            $contact_ids[] = $contact_search->contact_id;
        }
        return $contact_ids;
    }

    /**
     * Generate the SQL select clause for
     * @param array $config
     *   the values of the form
     *
     * @return string
     *   sql select clause to define the resource name
     */
    protected function getSelectClause($config)
    {
        switch ($config['label_pattern']) {
            default:
            case 'display_name':
                return 'contact.display_name';
        }
    }
}
