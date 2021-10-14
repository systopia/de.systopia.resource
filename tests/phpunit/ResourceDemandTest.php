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
class BasicResourceDemandTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                        TransactionalInterface
{
    /**
     * Create a simple resource
     */
    public function testSimpleCreateResourceDemand()
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
    public function testSimpleCreateResourceDemandWithCondition()
    {
        $resource_contact = $this->createContact();
        $created_resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $resource_contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);
        $demand_entity = $this->createContact();
        $created_resource_demand = $this->callAPI34('ResourceDemand', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $demand_entity['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        // todo: expose to API
        $resource_demand_bao = new CRM_Resource_BAO_ResourceDemand();
        $resource_demand_bao->id = $created_resource_demand['id'];
        $resource_demand_bao->find(true);

        $resource_bao = new CRM_Resource_BAO_Resource();
        $resource_bao->id = $created_resource['id'];
        $resource_bao->find(true);

        // create resource demand condition
        $condition = CRM_Resource_DemandCondition_Attribute::createCondition(
            $created_resource_demand['id'],
            'first_name',
            $resource_contact['first_name']);

        // the resource should meet the individual condition
        $condition_would_be_met = $condition->isFulfilledWithResource($resource_bao);
        $this->assertTrue($condition_would_be_met, "This resource should meet the condition");

        // the whole demand should be met
        $resource_would_be_met =  $resource_demand_bao->isFulfilledWithResource($resource_bao);
        $this->assertTrue($resource_would_be_met, "This resource should meet the demand");

        // create BAD resource demand condition
        $bad_condition = CRM_Resource_DemandCondition_Attribute::createCondition(
            $created_resource_demand['id'],
            'first_name',
            'OTHER' . $resource_contact['first_name']);

        // the resource should not meet the individual condition
        $condition_would_be_met = $bad_condition->isFulfilledWithResource($resource_bao);
        $this->assertFalse($condition_would_be_met, "This resource should not meet the condition");

        // the whole demand should not be met
        $resource_would_be_met = $resource_demand_bao->isFulfilledWithResource($resource_bao, false);
        $this->assertFalse($resource_would_be_met, "This resource should not meet the demand");
    }
}
