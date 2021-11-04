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
 * Form controller for event resource demands
 */
class CRM_Resource_Form_EventResourceDemands extends CRM_Event_Form_ManageEvent
{
    /** @var CRM_Resource_Page_ResourceDemandsView */
    protected $demand_view_page = null;

    /**
     * Set variables up before form is built.
     */
    public function preProcess()
    {
        parent::preProcess();
        $this->setSelectedChild('resourcedemands');
        $this->demand_view_page = new CRM_Resource_Page_ResourceDemandsView();
        $this->demand_view_page->setEntity('civicrm_event', $this->_id);
    }

    public function buildQuickForm()
    {
        $this->demand_view_page->setAssignSink($this);
        $this->demand_view_page->assignData();
        parent::buildQuickForm();
    }

    public function postProcess()
    {
//        $values = $this->exportValues();
//        // store values
//        $event_update = [
//            'id' => $this->_id,
//            'is_template'
//                    => CRM_Remoteevent_RemoteEvent::isTemplate($this->_id),
//            'event_alternative_location.event_alternativelocation_contact_id'
//                    => CRM_Utils_Array::value('event_alternativelocation_contact_id', $values),
//            'event_alternative_location.event_alternativelocation_remark'
//                    => CRM_Utils_Array::value('event_alternativelocation_remark', $values),
//        ];
//        CRM_Remoteevent_CustomData::resolveCustomFields($event_update);
//        civicrm_api3('Event', 'create', $event_update);

        $this->_action = CRM_Core_Action::UPDATE;
        parent::endPostProcess();
    }

    /**
     * Override the default template
     */
    public function __getTemplateFileName()
    {
        $demand_view_template = $this->demand_view_page->getTemplateFileName();
        return $demand_view_template;
    }

}
