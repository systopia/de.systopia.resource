<?php

use CRM_Resource_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Resource_Form_ConditionEdit extends CRM_Core_Form
{
    /** @var integer condition id */
    protected $condition_id = null;

    /** @var CRM_Resource_BAO_ResourceDemandCondition */
    protected $condition;

    /** @var CRM_Resource_BAO_ResourceDemand */
    protected $demand;

    public function buildQuickForm()
    {
        $this->condition_id = CRM_Utils_Request::retrieve('id', 'Integer', $this, true);

        // load condition
        /** @var CRM_Resource_BAO_ResourceDemandCondition  $bao */
        $bao = CRM_Resource_BAO_ResourceDemandCondition::findById($this->condition_id);
        if (empty($bao)) {
            throw new Exception("Unavailability [$this->condition_id] not found.");
        }
        $this->condition = $bao->getImplementation();

        // load demand
        $this->demand = CRM_Resource_BAO_ResourceDemand::findById($this->condition->resource_demand_id);

        // get the condition class
        $condition_types = CRM_Resource_BAO_ResourceDemandCondition::getAllConditionTypes($this->demand->entity_table);
        if (!in_array($this->condition->class_name, $condition_types)) {
            throw new Exception("Undefined condition type: {$this->condition->class_name}");
        }

        // get the labels
        $this->assign('current_label', $this->condition->getLabel());
        $type_fields = call_user_func([$this->condition->class_name, 'addFormFields'], $this, '', $this->demand);
        $this->assign('type_fields', $type_fields);
        $this->setDefaults($this->condition->getCurrentFormValues());

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Update'),
                  'isDefault' => true,
              ],
          ]);

        parent::buildQuickForm();
    }

    /**
     * Validate the user defined fields
     *
     * @return bool
     */
    public function validate()
    {
        $errors = call_user_func([$this->condition->class_name, 'validateFormSubmission'], $this->_submitValues);
        foreach ($errors as $field_key => $error) {
            $this->_errors[$field_key] = $error;
        }
        return (0 == count($this->_errors));
    }

    public function postProcess()
    {
        $values = $this->exportValues();
        $this->condition->parameters = json_encode(call_user_func([$this->condition->class_name, 'compileParameters'], $values));
        $this->condition->save();
        parent::postProcess();
    }

}