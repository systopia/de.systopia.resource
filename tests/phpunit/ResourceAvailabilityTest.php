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
class BasicResourceAvailabilityTest extends ResourceTestBase implements HeadlessInterface, HookInterface,
                                                                        TransactionalInterface
{
    /**
     * Test if the resource availability based on the "absolute unavailability" works
     */
    public function testAbsoluteUnavailability()
    {
        $contact = $this->createContact();
        $resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        // check if resource is available
        $this->assertResourceAvailable(true, $resource['id']);

        // add unavailability
        $unavailability = $this->callAPI34('ResourceUnavailability', 'create', [
            'resource_id' => $resource['id'],
            'class_name' => 'CRM_Resource_Unavailability_Absolute',
            'reason' => "testAbsoluteUnavailability"
        ]);

        // check if resource is still available
        $this->assertResourceAvailable(false, $resource['id']);

        // delete availability
        $this->callAPI34('ResourceUnavailability', 'delete', ['id' => $unavailability['id']]);

        // resource should be available again
        $this->assertResourceAvailable(true, $resource['id']);
    }


    /**
     * Test if the resource availability based on the "absolute unavailability" works
     */
    public function testDateRangeUnavailability()
    {
        $contact = $this->createContact();
        $resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        // check if resource is available
        $this->assertResourceAvailable(true, $resource['id'], ['from' => 'now']);

        // add unavailability
        $unavailability_BAO = CRM_Resource_Unavailability_DateRange::createUnavailability(
            $resource['id'],
            'testDateRangeUnavailability',
            'now - 1 day',
            'now + 1 day'
        );

        // check if resource is still available
        $this->assertResourceAvailable(false, $resource['id'], ['from' => 'now']);

        // delete availability
        $this->callAPI34('ResourceUnavailability', 'delete', ['id' => $unavailability_BAO->id]);

        // resource should be available again
        $this->assertResourceAvailable(true, $resource['id'], ['from' => 'now']);
    }

    /**
     * Test if the resource availability based on the "absolute unavailability" works
     */
    public function testIrrelevantDateRangeUnavailability()
    {
        $contact = $this->createContact();
        $resource = $this->callAPI34('Resource', 'create', [
            'resource_type_id' => ResourceTestBase::RESOURCE_TYPE_CONTACT,
            'entity_id' => $contact['id'],
            'entity_table' => 'civicrm_contact',
            'label' => $this->randomString()
        ]);

        // check if resource is available
        $this->assertResourceAvailable(true, $resource['id'], ['from' => 'now']);

        // add irrelevant unavailability
        $unavailability1 = CRM_Resource_Unavailability_DateRange::createUnavailability(
            $resource['id'],
            'test irrelevant future unavailability',
            'now + 1 day',
            'now + 2 day'
        );

        $unavailability2 = CRM_Resource_Unavailability_DateRange::createUnavailability(
            $resource['id'],
            'test irrelevant past unavailability',
            'now - 2 day',
            'now - 1 day'
        );

        // check if resource is still available
        $this->assertResourceAvailable(true, $resource['id'], ['from' => 'now']);
    }

}
