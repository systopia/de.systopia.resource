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
 * Delete the given resource demand and refresh
 *
 * @param demand_id
 */
function delete_resource_demand(demand_id) {
  CRM.api3('ResourceDemand', 'delete', {id:demand_id})
    .then(function() {
      // try refresh: tab
      let tab_content_id = cj("#tab_resourcedemands").attr('aria-controls');
      if (tab_content_id) {
        cj("#" + tab_content_id).crmSnippet('refresh');
        let ts = CRM.ts('de.systopia.resource');
        CRM.alert(ts("Resource Demand deleted"), ts("Deleted"), "info");
      }
    });
}
