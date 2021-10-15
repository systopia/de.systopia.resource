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
 * Resource.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_resource_create_spec(&$spec)
{
    // $spec['some_parameter']['api.required'] = 1;
}

/**
 * Resource.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_resource_create($params)
{
    return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'Resource');
}

/**
 * Resource.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_resource_delete($params)
{
    return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Resource.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_resource_get($params)
{
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, true, 'Resource');
}

/**
 * Resource.meets_demand API specification (optional).
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_resource_meets_demand_spec(&$spec)
{
    $spec['id']          = [
        'name'         => 'id',
        'api.required' => 1,
        'type'         => CRM_Utils_Type::T_INT,
        'title'        => E::ts('Resource ID'),
        'description'  => E::ts('Resource ID to be tested'),
    ];
    $spec['resource_demand_id'] = [
        'name'         => 'resource_demand_id',
        'api.required' => 1,
        'type'         => CRM_Utils_Type::T_INT,
        'title'        => E::ts('Resource Demand ID'),
        'description'  => E::ts('The demand that the resource should be tested against'),
    ];
    $spec['cached'] = [
        'name'         => 'cached',
        'api.default'  => false,
        'type'         => CRM_Utils_Type::T_BOOLEAN,
        'title'        => E::ts('Cache conditions?'),
        'description'  => E::ts('Caching might speed up the processing of multiple calls, but intermediate changes to the resource demand and conditions would be ignored.'),
    ];
}

/**
 * Resource.meets_demand API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_resource_meets_demand($params)
{
    // get resource
    $resource = new CRM_Resource_BAO_Resource();
    $resource->id = $params['id'];
    if (!$resource->find(true)) {
        throw new CiviCRM_API3_Exception("A resource with ID [{$params['id']}] doesn't exist (any more).");
    }

    // get demand
    $resource_demand = new CRM_Resource_BAO_ResourceDemand();
    $resource_demand->id = $params['resource_demand_id'];
    if (!$resource_demand->find(true)) {
        throw new CiviCRM_API3_Exception("A resource demand with ID [{$params['resource_demand_id']}] doesn't exist (any more).");
    }

    // check issues
    $violations = [];
    $fulfilled = $resource_demand->isFulfilledWithResource($resource, $params['cached'], $violations);
    if (!$fulfilled && empty($violations)) {
        // add a general violation
        $violations[] = E::ts("At least one criteria was not met.");
    }

    $dao = null;
    return civicrm_api3_create_success(null, $params, 'resource_demand', 'meets_demand', $dao, ['is_fulfilled' => $fulfilled, 'violations' => $violations]);
}
