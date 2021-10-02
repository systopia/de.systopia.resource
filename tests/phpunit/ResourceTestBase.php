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
use PHPUnit\Framework\TestCase;

/**
 * Resource API Test Case
 *
 * @group headless
 */
class ResourceTestBase extends TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    use \Civi\Test\Api3TestTrait {
        callAPISuccess as protected traitCallAPISuccess;
    }

    /**
     * Set up for headless tests.
     *
     * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
     *
     * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
     */
    public function setUpHeadless()
    {
        return Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * The setup() method is executed before the test is executed (optional).
     */
    public function setUp()
    {
        $table = CRM_Core_DAO_AllCoreTables::getTableForEntityName('Resource');
        $this->assertTrue(
            $table && CRM_Core_DAO::checkTableExists($table),
            'There was a problem with extension installation. Table for ' . 'Resource' . ' not found.'
        );
        parent::setUp();
    }

    /**
     * The tearDown() method is executed after the test was executed (optional)
     * This can be used for cleanup.
     */
    public function tearDown()
    {
        parent::tearDown();
    }


    // HELPER FUNCTIONS

    /**
     * Create a new contact
     *
     * @param array $contact_details
     *   overrides the default values
     *
     * @return array
     *  contact data
     */
    public function createContact($contact_details = [])
    {
        // prepare event
        $contact_data = [
            'contact_type' => 'Individual',
            'first_name'   => $this->randomString(10),
            'last_name'    => $this->randomString(10),
            'email'        => $this->randomString(10) . '@' . $this->randomString(10) . '.org',
            'prefix_id'    => 1,
        ];
        foreach ($contact_details as $key => $value) {
            $contact_data[$key] = $value;
        }
//        CRM_Remoteevent_CustomData::resolveCustomFields($contact_data);

        // create contact
        $result = $this->traitCallAPISuccess('Contact', 'create', $contact_data);
        $contact = $this->traitCallAPISuccess('Contact', 'getsingle', ['id' => $result['id']]);
        //CRM_Remoteevent_CustomData::labelCustomFields($contact);
        return $contact;
    }

    /**
     * Create a number of new contacts
     *  using the createContact function above
     *
     * @param integer $count
     * @param array $contact_details
     *
     * @return array [event_id => $event_data]
     */
    public function createContacts($count, $contact_details = [])
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $contact = $this->createContact($contact_details);
            $result[$contact['id']] = $contact;
        }
        return $result;

    }

    /**
     * Generate a random string, and make sure we don't collide
     *
     * @param int $length
     *   length of the string
     *
     * @return string
     *   random string
     */
    public function randomString($length = 32)
    {
        static $generated_strings = [];
        $candidate = substr(sha1(random_bytes(32)), 0, $length);
        if (isset($generated_strings[$candidate])) {
            // simply try again (recursively). Is this dangerous? Yes, but veeeery unlikely... :)
            return $this->randomString($length);
        }
        // mark as 'generated':
        $generated_strings[$candidate] = 1;
        return $candidate;
    }


    /**
     * Get a random value subset of the array
     *
     * @param array $array
     *   the array to pick the values
     *
     * @param integer $count
     *   number of elements to pick, will be randomised if not given
     *
     * @return array
     *   subset (keys not retained)
     */
    public function randomSubset($array, $count = null)
    {
        if ($count === null) {
            $count = mt_rand(1, count($array) - 1);
        }

        $random_keys = array_rand($array, $count);
        if (!is_array($random_keys)) {
            $random_keys = [$random_keys];
        }

        // create result array
        $result = [];
        foreach ($random_keys as $random_key) {
            $result[] = $array[$random_key];
        }
        return $result;
    }
}
