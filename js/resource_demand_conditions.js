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

(function ($, _, ts) {
  $(document).ready(function () {

    $('.resource-demand-view-conditions .action--resource--demand_condition-delete')
      .on('click', function (event) {
        var condition_id = $(this).data('condition-id');
        CRM.confirm({
          title: ts("Confirm Deletion"),
          message: ts("Do you really want to delete this condition?")
        }).on('crmConfirm:yes', function () {
          CRM.api3('ResourceDemandCondition', 'delete', {id: condition_id})
            .then(function () {
              CRM.alert(ts("Condition deleted"), ts("Deleted"), "info");
              CRM.refreshParent(event);
              $(event.target)
                .closest('.crm-ajax-container, #crm-main-content-wrapper')
                .trigger('crmPopupFormSuccess');
            });
        });
        // Avoid fragment jump.
        return false;
      });
  });

})(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
