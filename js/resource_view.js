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
  CRM.api3('ResourceUnavailability', 'delete', {id:unavailability_id})
    .then(function() {
      // try refresh: tab
      let tab_content_id = cj("#tab_resource").attr('aria-controls');
      if (tab_content_id) {
        cj("#" + tab_content_id).crmSnippet('refresh');
        let ts = CRM.ts('de.systopia.resource');
        CRM.alert(ts("Resource Unavailability deleted"), ts("Deleted"), "info");
      }
    });
}
