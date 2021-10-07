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

use Civi\Test;
use Civi\Test\Api3TestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

require_once 'ResourceTestBase.php';

/**
 * Resource API Test Case
 *
 * @group headless
 */
class BasicResourceTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                      TransactionalInterface
{
    /**
     * Simple resource creation
     */
    public function testCreateContactResource()
    {
        $contact = $this->createContact();
        $created_resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        $loaded_resource = $this->callAPI34('Resource', 'getsingle', [
            'id' => $created_resource['id'],
        ]);
    }

    /**
     * Create a simple resource
     */
    public function testCreateResourceDemand()
    {
        $contact = $this->createContact();
        $created_resource_demand = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        $loaded_demand = $this->callAPI34('ResourceDemand', 'getsingle', [
            'id' => $created_resource_demand['id'],
        ]);

        $created_resource_demand_template = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'is_template' => 1,
            'label' => $this->randomString()
        ]);

        $loaded_demand_template = $this->callAPI34('ResourceDemand', 'getsingle', [
            'id' => $created_resource_demand_template['id'],
        ]);
    }

    /**
     * Create a simple resource
     */
    public function testCreateResourceAssignment()
    {
        $contact = $this->createContact();
        $resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
        $resource_demand = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        $resource_assignment = $this->callAPI34('ResourceAssignment', 'create', [
            'resource_id' => $resource['id'],
            'resource_demand_id' => $resource_demand['id'],
            'status' => 1
        ]);

        $loaded_assignment = $this->callAPI34('ResourceAssignment', 'getsingle', [
            'id' => $resource_assignment['id'],
        ]);
    }

}
