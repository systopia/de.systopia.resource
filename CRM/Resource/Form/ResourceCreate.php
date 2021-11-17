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
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Resource_Form_ResourceCreate extends CRM_Core_Form
{
    /** @var integer the entity ID this resource is for */
    protected $entity_id;

    /** @var string the entity table this resource is for */
    protected $entity_table;

    public function buildQuickForm()
    {
        $this->entity_id = CRM_Utils_Request::retrieve('entity_id', 'Integer', $this);
        $this->entity_table = CRM_Utils_Request::retrieve('entity_table', 'String', $this);

        $resources = civicrm_api3('Resource', 'get', [
            'entity_id' => $this->entity_id,
            'entity_table' => $this->entity_table,
        ]);
        if ($resources['count']) {
            CRM_Utils_System::redirect(
                CRM_Utils_System::url(
                    'civicrm/resource/view',
                    [
                        'id' => reset($resources['values'])['id']
                    ]
                )
            );
        }

        // add form elements
        $this->add(
            'select',
            'resource_type',
            E::ts('Resource Type'),
            $this->getResourceTypes(),
            true,
            ['class' => 'crm-select2']
        );

        $this->add(
            'text',
            'resource_name',
            E::ts('Resource Label'),
            ['class' => 'huge'],
            true
        );

        $this->addButtons([
              [
                  'type' => 'submit',
                  'name' => E::ts('Create Resource'),
                  'icon' => 'fa-magic',
                  'isDefault' => true,
              ],
          ]);

        // add some data
        $this->assign('entity_name', E::ts(CRM_Resource_Types::getEntityName($this->entity_table)));
        $this->setDefaults([
            'resource_name' => $this->getDefaultLabel()
        ]);

        parent::buildQuickForm();
    }


    public function postProcess()
    {
        $values = $this->exportValues();
        $resource = civicrm_api3('Resource', 'create', [
            'resource_type_id' => $values['resource_type'],
            'entity_id' => $this->entity_id,
            'entity_table' => $this->entity_table,
            'label' => $values['resource_name'],
        ]);
        if (CRM_Core_Resources::isAjaxMode()) {
            $resourceBao = CRM_Resource_BAO_Resource::getInstance($resource['id']);
            $this->ajaxResponse['updateTabs']['#tab_resource'] = count($resourceBao->getAssignedDemands());
        }

        // reload the page
        CRM_Utils_System::redirect(CRM_Core_Session::singleton()->popUserContext());
    }

    /**
     * Get the resource types for the given entity_table
     *
     * @return array
     *  resource types
     */
    protected function getResourceTypes()
    {
        $resource_types = CRM_Resource_Types::getForEntityTable($this->entity_table);
        $resource_type_options = [];
        foreach ($resource_types as $resource_type) {
            $resource_type_options[$resource_type['id']] = $resource_type['label'];
        }
        return $resource_type_options;
    }

    /**
     * Get the default label for the resource
     *
     * @return string
     *  resource types
     */
    protected function getDefaultLabel()
    {
        switch ($this->entity_table) {
            case 'civicrm_contact':
                return civicrm_api3('Contact', 'getvalue', ['return' => 'display_name', 'id' => $this->entity_id]);

            default:
                $entity_name = E::ts(CRM_Resource_Types::getEntityName($this->entity_table));
                try {
                    $bao = CRM_Core_DAO_AllCoreTables::getClassForTable($this->entity_table);
                    if ($label_field = $bao::$_labelField) {
                        $entity_label = civicrm_api3($entity_name, 'getvalue', [
                            'return' => $label_field,
                            'id' => $this->entity_id,
                        ]);
                    }
                    else {
                        throw new Exception('Could not determine entity label.');
                    }
                }
                catch (Exception $esception) {
                    $entity_label = $entity_name;
                }
                return E::ts("%1 Resource [%2]", [1 => $entity_label, 2 => $this->entity_id]);
        }
    }
}
