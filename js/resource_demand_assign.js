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
  // add assign checkbox handler
  cj("input[id^=assign_]").change(function() {
    // count the selected checkboxes and see if it's too many
    let checked_checkboxes = cj("input[id^=assign_]").filter(":checked").length;
    if (checked_checkboxes > CRM.vars.resource_demand_assign.assigned_missing) {
      cj(".resource-too-many")
        .show(100);
    } else {
      cj(".resource-too-many")
        .hide(100);
    }
  });

  // add 'select all' handler
  cj("span.resource-all").click(function() {
    cj("input[id^=assign_]")
      .prop('checked', true)
      .change();
  });
});

