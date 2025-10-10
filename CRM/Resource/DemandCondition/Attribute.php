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
 * This resource demand tests for exactly one attribute of the target resource.
 *
 * The implementation is based on APIv4 interface
 */
class CRM_Resource_DemandCondition_Attribute extends CRM_Resource_BAO_ResourceDemandCondition
{
    /**
     * Get the proper label for this unavailability
     *
     * @return string
     *    the label of this unavailability type
     */
    public static function getTypeLabel()
    {
        return E::ts("Attribute Value (experimental)");
    }

    /**
     * Get an font-awesome icon for this condition
     */
    public function getIcon()
    {
        return 'fa-question';
    }

    /**
     * Get the entity name in the APIv4 linked to the resource demand
     *
     * @param CRM_Resource_BAO_ResourceDemand $resource_demand
     *   the resource demand
     *
     * @return string entity name
     */
    public static function getApi4Entity($resource_demand)
    {
        // todo: adjustments needed?
        $resource_type = CRM_Resource_Types::getType($resource_demand->resource_type_id);
        return CRM_Core_DAO_AllCoreTables::getEntityNameForTable($resource_type['entity_table']);
    }

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
    public static function createCondition(string $resource_demand_id, string $attribute_name, string $value, string $operation = '='): CRM_Resource_DemandCondition_Attribute
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
     *    the resource to be tested against
     *
     * @param array $error_messages
     *    if there is a problem, add a description to the list of error messages
     *
     * @return boolean does the resource fulfill this condition
     *
     * @note this should be overwritten by the subclass implementation
     */
    public function isFulfilledWithResource($resource, &$error_messages = []) : bool
    {
        $addOr = FALSE;
        $clauseStatement = [];

        $params = $this->getParametersParsed();
        if (empty($params) || count($params) < 3) {
            Civi::log()->warning("Garbled parameters for condition [{$this->id}]");
            return false;
        }
        $entity_name = self::getApi4Entity($this->getResourceDemand());
        $attribute_name = $params[0];
        // Fallback to distinguish the APIv3 custom field name
        if (substr( $attribute_name, 0, 7) === "custom_") {
          $customFieldID = substr($attribute_name, 7);
          $field_specs = civicrm_api4($entity_name, 'getFields', [
            'where' => [['custom_field_id', '=', $customFieldID]],
          ]);
          $field_spec = $field_specs->first();
          // Set the proper attribute name
          $attribute_name = $field_spec['name'];
        }
        $attribute_value = $params[1];
        $attribute_operation = $params[2];
        $sqlOp = self::getSQLOperator(html_entity_decode($attribute_operation));
        // Rework for some operators
        // In the case of multiselect customfields, we need to work on the attribute values first as the multiple values are being stored as a string with separators
        switch ($sqlOp) {
          case 'LIKE':
          case 'NOT LIKE':
            if ($attribute_operation == 'contains one or more' || $attribute_operation == 'not contains one or more') {
              $addOr = TRUE;
              foreach ($attribute_value as $atrKey) {
                $attribute_value_tmp[] = '%' . CRM_Core_DAO::VALUE_SEPARATOR . $atrKey . CRM_Core_DAO::VALUE_SEPARATOR . '%';
              }
              $attribute_value = $attribute_value_tmp;
            }
            else {
              $attribute_value = '%' . CRM_Core_DAO::VALUE_SEPARATOR . $attribute_value . CRM_Core_DAO::VALUE_SEPARATOR . '%';
            }
            break;
        }

        // Prepare the statement
        $resources = \Civi\Api4\Resource::get()
            ->setJoin([["{$entity_name} AS entity", TRUE, NULL, ['entity_id', '=', 'entity.id']]])
            ->addWhere("id", '=', $resource->id)
            ->setLimit(1);
        // TODO: Fix the NULL vs empty
        if ($addOr && is_array($attribute_value)) {
          foreach ($attribute_value as $atrKey) {
            $clauseStatement[] = ["entity.{$attribute_name}", $sqlOp, $atrKey];
          }
          if ($sqlOp == 'NOT LIKE') {
            $resources->addClause('AND', $clauseStatement);
          }
          else {
            $resources->addClause('OR', $clauseStatement);
          }

        }
        else {
          $resources->addWhere("entity.{$attribute_name}", $sqlOp, $attribute_value);
        }

        // Execute the statement
        $count = $resources->execute()->count();
        return $count > 0;
    }

    /**
     * Get the proper label for this unavailability
     */
    public function getLabel()
    {
        // todo: improve
        $params = $this->getParametersParsed();

        $optionLabel = $params[1];
        $showOptions = $nullOp = FALSE;

        $excludeOL = [
          'is empty',
          'is not empty',
        ];

        if (in_array($params[2], $excludeOL)) {
          $nullOp = TRUE;
        }

        $entity_name = self::getApi4Entity($this->getResourceDemand());
        // Workaround to support lookup on customfields coming as `custom_xxx`
        if (substr( $params[0], 0, 7) === "custom_") {
          $customFieldID = substr($params[0], 7);
          $field_specs = civicrm_api4($entity_name, 'getFields', [
            'where' => [['custom_field_id', '=', $customFieldID]],
            'loadOptions' => TRUE,
          ]);
          $showOptions = TRUE;
        }
        else {
          $field_specs = civicrm_api4($entity_name, 'getFields', [
            'where' => [['name', '=', $params[0]]],
          ]);
        }
        $field_spec = $field_specs->first();

        // Prepare to display the optionvalue labels
        if ($showOptions && !$nullOp) {
          if (is_array($params[1])) {
            $optionLabel = [];
            foreach ($params[1] as $ov) {
              if (array_key_exists($ov, $field_spec['options'])) {
                $optionLabel[] = $field_spec['options'][$ov];
              }
            }
          }
          else {
            if (array_key_exists($params[1], $field_spec['options'])) {
              $optionLabel = $field_spec['options'][$params[1]];
            }
          }

          $returnValue = E::ts("Attribute \"%1\" <code>%2</code> \"%3\"",
          [
              1 => $field_spec['label'],
              2 => $params[2],
              3 => trim(json_encode($optionLabel), '"'),
          ]
          );

        }
        else {
          // Null operator, just display the rest
          $returnValue = E::ts("Attribute \"%1\" <code>%2</code> \"%3\"",
          [
              1 => $field_spec['label'],
              2 => $params[2],
              3 => NULL,
          ]
          );

        }

        return $returnValue;
    }

    /*****************************************
     ***          FORM INTEGRATION          **
    /****************************************/

    /**
     * Add form fields for the given unavailability
     *
     * @param $form CRM_Core_Form
     *   a form the parameters should be added to
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @param $demand_bao CRM_Resource_BAO_ResourceDemand
     *   the resource demand this condition belongs to
     *
     * @return array
     *    list of field keys (incl. prefix)
     */
    public static function addFormFields($form, $prefix = '', $demand_bao = null)
    {
        // extract field names
        $field_names = [];
        $entity_name = self::getApi4Entity($demand_bao);
        $field_specs = civicrm_api4($entity_name, 'getFields');
        foreach ($field_specs->getIterator() as $field_spec) {
          // As we are not ready yet on the APIv4 on the frontend side, we need
          // to convert the Customfieldgroup.customfieldname to custom_xxx.customfieldname
          if ($field_spec['type'] == 'Custom') {
            $field_names["custom_" . $field_spec['custom_field_id']] = $field_spec['title'];
          }
          else {
            $field_names[$field_spec['name']] = $field_spec['title'];
          }
        }

        $operators = [
          '=' => E::ts('Is equal to'),
          '!=' => E::ts('Is not equal to'),
          '>' => E::ts('Is greater than'),
          '<' => E::ts('Is less than'),
          '>=' => E::ts('Is greater than or equal to'),
          '<=' => E::ts('Is less than or equal to'),
          'contains string' => E::ts('Contains string (case insensitive)'),
          'contains one or more' => E::ts('Contains one or more string(s) (case insensitive)'),
          'not contains string' => E::ts('Does not contain string (case insensitive)'),
          'not contains one or more' => E::ts('Does not contain one or more string(s) (case insensitive)'),
          'is empty' => E::ts('Is empty'),
          'is not empty' => E::ts('Is not empty'),
          'is one of' => E::ts('Is one of'),
          'is not one of' => E::ts('Is not one of'),
        ];

        $form->add(
            'select',
            $prefix . '_field_name',
            E::ts("Field"),
            $field_names,
            true,
            ['class' => 'crm-select2']
        );

        $form->add(
            'select',
            $prefix . '_operator',
            E::ts("Operator"),
            $operators,
            true,
            ['class' => 'crm-select2']
        );

        $form->add('text', $prefix . '_value', E::ts('Value'), NULL, FALSE);
        $form->add('textarea', $prefix . '_multi_value', E::ts('Values'));

        return [
            $prefix . '_field_name',
            $prefix . '_operator',
            $prefix . '_value',
            $prefix . '_multi_value',
        ];
    }

    /**
     * Validate our values in the form submission
     *
     * @param $submit_values array
     *   the submitted values
     *
     * @return array
     *    validation errors [field_name => error]
     */
    public static function validateFormSubmission($submit_values, $prefix = '')
    {
        // todo: does there have to be any validation
        return [];
    }

    /**
     * Generate data values
     *
     * @param $data array
     *   form data
     *
     * @param $prefix string
     *   the prefix to be used to make sure there is no clash in forms
     *
     * @return array
     *   the data that should be written into the parameters field as a json blob
     */
    public static function compileParameters($data, $prefix = '')
    {
      $isMultiple = self::getEntryType($data["{$prefix}_operator"]);
      if ($isMultiple) {
        if (array_key_exists("{$prefix}_multi_value", $data) && !empty($data["{$prefix}_multi_value"])) {
          $dataValues = preg_split('/\n|\r\n?/', $data["{$prefix}_multi_value"]);
        }
        else {

        }
      }
      else {
        $dataValues = $data["{$prefix}_value"];
      }

        // format the from/to values properly
        return [
            $data["{$prefix}_field_name"],
            $dataValues,
            $data["{$prefix}_operator"],
        ];
    }

    /**
     * getEntryType
     * Identifies if the field is multiple or single
     *
     * @param  mixed $op
     * @return bool
     */
    private function getEntryType($Op) {
      $isMultiple = FALSE;

      $operators = [
        '=' => E::ts('Is equal to'),
        '!=' => E::ts('Is not equal to'),
        '>' => E::ts('Is greater than'),
        '<' => E::ts('Is less than'),
        '>=' => E::ts('Is greater than or equal to'),
        '<=' => E::ts('Is less than or equal to'),
        'contains string' => E::ts('Contains string (case insensitive)'),
        'not contains string' => E::ts('Does not contain string (case insensitive)'),
        'is empty' => E::ts('Is empty'),
        'is not empty' => E::ts('Is not empty'),
        'is one of' => E::ts('Is one of'),
        'is not one of' => E::ts('Is not one of'),
        'contains one or more' => E::ts('Contains one or more string(s) (case insensitive)'),
        'not contains one or more' => E::ts('Does not contain one or more string(s) (case insensitive)'),
      ];
      if ($Op) {
        switch ($Op) {
          case 'is one of':
          case 'is not one of':
          case 'contains one or more':
          case 'not contains one or more':
            $isMultiple = TRUE;
            break;

          case 'is empty':
          case 'is not empty':
            $isMultiple = FALSE;
            break;

          default:
            $isMultiple = FALSE;
          break;

        }
      }
      return $isMultiple;

    }

    /**
     * getSQLOperator
     * Converts the text operator to SQL operator
     *
     * @param  mixed $op
     * @return string
     */
    private function getSQLOperator($op) {
      $sqlOp = $op;

        $operators = [
          '=' => '=',
          '!=' => '!=',
          '>' => '>',
          '<' => '<',
          '>=' => '>=',
          '<=' => '<=',
          'contains string' => 'LIKE',
          'contains one or more' => 'LIKE',
          'not contains string' => 'NOT LIKE',
          'not contains one or more' => 'NOT LIKE',
          'is empty' => 'IS NULL',
          'is not empty' => 'IS NOT NULL',
          'is one of' => 'IN',
          'is not one of' => 'NOT IN',
        ];
        if (array_key_exists($op, $operators)) {
          $sqlOp = $operators[$op];
        }

        return $sqlOp;

    }

    /**
     * Get the current values for the fields defined in ::addFormFields
     *
     * @param string $prefix
     *   an optional prefix
     *
     * @return array
     *   field-key => current value
     */
    public function getCurrentFormValues($prefix = '')
    {
        $params = $this->getParametersParsed();
        $defaults = [];

        if (isset($params[2])) {
          $opType = self::getEntryType($params[2]);
          // Check the value
          if ($opType) {
            if (is_array($params[1])) {
              $dataValue = implode("\r\n", $params[1]);
              $defaults = [
                "{$prefix}__field_name"  => $params[0],
                "{$prefix}__value"       => NULL,
                "{$prefix}__multi_value" => $dataValue,
                "{$prefix}__operator"    => $params[2],
                "condition_type"        => $prefix,
              ];
            }
          }
          else {
            $defaults = [
              "{$prefix}__field_name"  => $params[0],
              "{$prefix}__value"       => $params[1],
              "{$prefix}__multi_value" => NULL,
              "{$prefix}__operator"    => $params[2],
              "condition_type"        => $prefix,
            ];
          }
            return $defaults;
        }
        else {
            return [];
        }
    }
}
