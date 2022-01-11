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
    // add 'select all' handler
    $("span.resource-all").click(function () {
      let checked = $("input[id^=unassign_]").prop('checked');
      $("input[id^=unassign_]")
        .prop('checked', !checked)
        .change();
    });
  });
})(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
