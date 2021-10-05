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
     * Will call the API with the given parameters.
     * However, it will call APIv3 _and_ APIv4 in parallel,
     * and assert that the results are identical unless it's a
     * 'create'
     *
     * @param $entity string API entity
     * @param $action string API action
     * @param $params array API parameters
     *
     */
    public function callAPI34($entity, $action, $params)
    {
        if ($action == 'create' || $action == 'delete') {
            if (empty($params['version']) || $params['version'] == 3) {
                // create with APIv3
                return $this->traitCallAPISuccess($entity, $action, $params);
            } else {
                // create with APIv4
                try {
                    return civicrm_api4($entity, $action, $params);
                } catch (API_Exception $ex) {
                    $this->fail("APIv4 error: ", $ex->getMessage());
                }
            }
        }

        // run both:
        $params['version'] = 3;
        $result_v3 = $this->traitCallAPISuccess($entity, $action, $params);
        $params['version'] = 4;
        $result_v4 = $this->traitCallAPISuccess($entity, $action, $params);

        // compare top level
        $skip_keys = ['xdebug', 'values'];
        $this->assertApiValuesEqual($result_v3, $result_v4, $skip_keys);
        // compare values
        if (isset($result_v3['values'])) {
            $this->assertApiValuesEqual($result_v3['values'], $result_v4['values'], $skip_keys);
        }

        return $result_v3;
    }

    /**
     * Internal function to compare the values of an apiv3 and apiv4 result
     * @param $values_v3
     * @param $values_v4
     * @param array $skip_keys
     */
    public function assertApiValuesEqual($values_v3, $values_v4, $skip_keys = [])
    {
        $keys = array_unique(array_merge(array_keys($values_v3), array_keys($values_v4)));
        foreach ($keys as $key) {
            if (in_array($key, $skip_keys)) continue;
            $value_v3 = $values_v3[$key] ?? null;
            $value_v4 = $values_v3[$key] ?? null;
            if (is_array($value_v3) && is_array($value_v4)) {
                $this->assertApiValuesEqual($value_v3, $value_v4, $skip_keys);
            } else {
                $this->assertEquals($value_v3, $value_v4, "APIv3 value differs from APIv4 value.");
            }
        }
    }



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


    /**
     * Assert that the resource is available wrt the res
     * @param $resource_id
     * @param $parameters
     * @param $expected
     */
    public function assertResourceAvailable($resource_id, $parameters, $expected)
    {
        // use the BAO function: todo: expose to API
        $resource_bao = new CRM_Resource_BAO_Resource();
        $resource_bao->id = $resource_id;
        $available = $resource_bao->isAvailable($parameters['from'] ?? null, $parameters['to'] ?? null);

        // todo use SQL
        //$resource_bao->isAvailableSQL();
    }
}
