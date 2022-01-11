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

  $('.resource-view-unavailabilities .action--resource--unavailability-delete')
    .on('click', function(event, target) {
      delete_unavailability($(this).data('unavailability-id'));
    });

  $('.resource-view-unavailabilities .action--resource--assignment-delete')
    .on('click', function(event, target) {
      delete_assignment($(this).data('assignment-id'));
    });

  /**
   * Delete the given resource unavailability and refresh
   *
   * @param unavailability_id
   */
  function delete_unavailability(unavailability_id) {
    CRM.confirm({
      title: ts("Confirm Deletion"),
      message: ts("Do you really want to delete this availability restriction?"),
    }).one('crmConfirm:yes', function () {
      CRM.api3('ResourceUnavailability', 'delete', {id: unavailability_id})
        .then(function () {
          // try refresh: tab
          let tab_content_id = $("#tab_resource").attr('aria-controls');
          if (tab_content_id) {
            $("#" + tab_content_id).crmSnippet('refresh');
            CRM.alert(ts("Resource Unavailability deleted"), ts("Deleted"), "info");
          }
        });
    });
  }

  /**
   * Delete the given resource assignment
   *
   * @param assignment_id
   */
  function delete_assignment(assignment_id) {
    CRM.confirm({
      title: ts("Delete Assignment"),
      message: ts("Do you really want to un-assign this resource?"),
    }).on('crmConfirm:yes', function () {
      CRM.api3('ResourceAssignment', 'delete', {id: assignment_id})
        .then(function () {
          // try refresh: tab
          let tab_content_id = $("#tab_resource").attr('aria-controls');
          if (tab_content_id) {
            $("#" + tab_content_id).crmSnippet('refresh');
            CRM.alert(ts("Resource Assignment Removed"), ts("Unassigned"), "info");
          }
        });
    });
  }
})(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
