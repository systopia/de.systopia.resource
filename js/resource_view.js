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

/**
 * Delete the given resource unavailability and refresh
 *
 * @param unavailability_id
 */
function delete_unavailability(unavailability_id) {
  let ts = CRM.ts('de.systopia.resource');
  CRM.confirm({
    title: ts("Confirm Deletion"),
    message: ts("Do you really want to delete this availability restriction?"),
  }).one('crmConfirm:yes', function() {
    CRM.api3('ResourceUnavailability', 'delete', {id: unavailability_id})
      .then(function () {
        // try refresh: tab
        let tab_content_id = cj("#tab_resource").attr('aria-controls');
        if (tab_content_id) {
          cj("#" + tab_content_id).crmSnippet('refresh');
          (function($, _, ts) {
            CRM.alert(ts("Resource Unavailability deleted"), ts("Deleted"), "info");
          })(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
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
  }).on('crmConfirm:yes', function() {
    CRM.api3('ResourceAssignment', 'delete', {id:assignment_id})
      .then(function() {
        // try refresh: tab
        let tab_content_id = cj("#tab_resource").attr('aria-controls');
        if (tab_content_id) {
          cj("#" + tab_content_id).crmSnippet('refresh');
          (function($, _, ts) {
            CRM.alert(ts("Resource Assignment Removed"), ts("Unassigned"), "info");
          })(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
        }
      });
  });
}
