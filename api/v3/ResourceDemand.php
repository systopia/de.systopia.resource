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
function _civicrm_api3_resource_demand_create_spec(&$spec)
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
 * @throws CRM_Core_Exception
 */
function civicrm_api3_resource_demand_create($params)
{
    return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'ResourceDemand');
}

/**
 * Resource.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_resource_demand_delete($params)
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
 * @throws CRM_Core_Exception
 */
function civicrm_api3_resource_demand_get($params)
{
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, true, 'ResourceDemand');
}
