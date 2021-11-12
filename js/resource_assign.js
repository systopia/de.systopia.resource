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

cj(document).ready(function() {
  //
  cj("button.resource-demand-assign").click(function() {
    // first: disable all buttons
    cj("button.resource-demand-assign").attr("disabled","disabled");

    // evaluate our button:
    let demand_id = cj(this).attr('data-demand-id');
    let resource_id = cj(this).attr('data-resource-id');
    CRM.api3('ResourceAssignment', 'create', {resource_demand_id:demand_id,resource_id:resource_id,status:3})
      .then(function() {
        // let them know
        let ts = CRM.ts('de.systopia.resource');
        CRM.alert(ts("Resource Assigned"), ts("Assigned"), "info");

        // refresh the popup
        cj("button.resource-demand-assign")
          .closest("div.crm-ajax-container")
          .crmSnippet('refresh');

        // try refresh: tab
        let tab_content_id = cj("#tab_resource").attr('aria-controls');
        if (tab_content_id) {
          cj("#" + tab_content_id).crmSnippet('refresh');
          let ts = CRM.ts('de.systopia.resource');
        }
      });
  });
});

