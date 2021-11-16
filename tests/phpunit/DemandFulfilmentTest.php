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
class DemandFulfilmentTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                        TransactionalInterface
{
    /**
     * Create a simple resource
     */
    public function testSimpleFulfilment()
    {
        // create a resource demand
        $contact = $this->createContact();
        $created_resource_demand = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
        // add a temporal condition
        CRM_Resource_DemandCondition_AbsoluteTime::createCondition(
            $created_resource_demand['id'],
            strtotime("now - 10 minutes"),
            strtotime("now + 10 minutes"),
        );

        // create two resources
        $resource_contact_1 = $this->createContact();
        $created_resource_1 = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $resource_contact_1['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
        $resource_contact_2 = $this->createContact();
        $created_resource_2 = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $resource_contact_2['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        // now, they both should meet the demand
        $result = $this->callAPI34('Resource', 'meets_demand', [
            'id' => $created_resource_1['id'],
            'resource_demand_id' => $created_resource_demand['id']
        ]);
        $this->assertTrue($result['is_fulfilled'], "Demand should be met with this resource.");

        $result = $this->callAPI34('Resource', 'meets_demand', [
            'id' => $created_resource_2['id'],
            'resource_demand_id' => $created_resource_demand['id']
        ]);
        $this->assertTrue($result['is_fulfilled'], "Demand should be met with this resource.");


        // BUT NOW: we'll create another demand and assign resource_1
        $conflicting_resource_demand = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
        CRM_Resource_DemandCondition_AbsoluteTime::createCondition(
            $conflicting_resource_demand['id'],
            strtotime("now - 10 minutes"),
            strtotime("now + 10 minutes"),
        );
        $this->assignResourceToDemand($created_resource_2['id'], $conflicting_resource_demand['id']);

        // and we should find, that resource_1 cannot fulfill the original resource demand any more
        $result = $this->callAPI34('Resource', 'meets_demand', [
            'id' => $created_resource_2['id'],
            'resource_demand_id' => $created_resource_demand['id']
        ]);
        $this->assertFalse($result['is_fulfilled'], "Demand should NOT be met with this resource any more - conflicting assignment.");
    }
}
