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
 * This resource demand tests for exactly one attribute of the target resource
 */
class CRM_Resource_DemandCondition_Attribute extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Create a new AttributeResourceDemandCondition
     *
     * @param integer $resource_demand_id
     *   resource demand ID
     *
     * @param string $attribute_name
     *   the name of the attribute to check
     *
     * @param mixed $value
     *   the value to check for
     *
     * @param string $operation
     *   the operation to execute
     *
     * @return \CRM_Resource_BAO_ResourceDemandCondition
     */
    public static function createCondition(string $resource_demand_id, string $attribute_name, string $value, string $operation = '=='): CRM_Resource_DemandCondition_Attribute
    {
        $params = [
            'resource_demand_id' => $resource_demand_id,
            'class_name'  => 'CRM_Resource_DemandCondition_Attribute',
        ];

        // "pack" the parameters
        $params['parameters'] = json_encode([$attribute_name, $value, $operation]);

        // we're good, run the creation
        /** @var CRM_Resource_BAO_ResourceDemandCondition $condition_bao */
        $condition_bao = CRM_Resource_BAO_ResourceDemandCondition::create($params);
        return $condition_bao->getImplementation();
    }

    /**
     * Check if the given condition is currently met
     *
     * @param \CRM_Resource_BAO_Resource $resource
     *
     * @return boolean does the resource fulfill this condition?
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function isFulfilledWithResource($resource): bool
    {
        $entity = $resource->getEntity();
        [$attribute_name, $value, $operation] = $this->getParameters();
        $current_value = $entity->$attribute_name ?? null;
        switch ($operation) {
            case '==':
                return $current_value == $value;
            case '!=':
                return $current_value != $value;
            default:
                Civi::log()->debug("DemandCondition_Attribute: operation {$operation} unknown.");
                return false;
        }
    }
}
