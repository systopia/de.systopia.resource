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
 * Edit existing Unavailability form
 */
class CRM_Resource_Form_UnavailabilityEdit extends CRM_Core_Form
{
    /** @var integer unavailability id */
    protected $unavailability_id = null;

    /** @var CRM_Resource_BAO_ResourceUnavailability */
    protected $unavailability;

    /** @var array list of unavailability_types */
    protected $unavailability_types;

    public function buildQuickForm()
    {
        $this->unavailability_id = CRM_Utils_Request::retrieve('id', 'Integer', $this, true);

        /** @var CRM_Resource_BAO_ResourceUnavailability  $bao */
        $bao = CRM_Resource_BAO_ResourceUnavailability::findById($this->unavailability_id);
        if (empty($bao)) {
            throw new Exception("Unavailability [$this->unavailability_id] not found.");
        }
        $this->unavailability = $bao->getImplementation();
        $unavailability_class = $this->unavailability->class_name;

        // get the unavailability class
        $unavailability_types = CRM_Resource_BAO_ResourceUnavailability::getAllUnavailabilityTypes();
        if (!in_array($this->unavailability->class_name, $unavailability_types)) {
            throw new Exception("Undefined unavailability type: {$this->unavailability->class_name}");
        }

        // get the labels
        $this->assign('current_label', $this->unavailability->getLabel());
        $type_fields = call_user_func([$unavailability_class, 'addFormFields'], $this);
        $this->assign('type_fields', $type_fields);

        $this->setDefaults(['reason' => $this->unavailability->reason]);
        $this->setDefaults($this->unavailability->getCurrentFormValues());

        // add form elements
        $this->add(
            'text',
            'reason',
            E::ts("Reason"),
            ['class' => 'huge'],
            true
        );

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
        $errors = call_user_func([$this->unavailability->class_name, 'validateFormSubmission'], $this->_submitValues);
        foreach ($errors as $field_key => $error) {
            $this->_errors[$field_key] = $error;
        }
        return (0 == count($this->_errors));
    }


    public function postProcess()
    {
        $values = $this->exportValues();
        $this->unavailability->reason = $values['reason'];
        $this->unavailability->parameters = json_encode(call_user_func([$this->unavailability->class_name, 'compileParameters'], $values));
        $this->unavailability->save();
        parent::postProcess();
    }

}
