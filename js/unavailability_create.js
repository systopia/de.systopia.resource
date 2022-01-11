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

    /**
     * Will only show the form fields relevant for the selected
     *  unavailability type
     */
    function only_show_relevant_fields() {
      let current_type = $('#unavailability_type').val();
      $("div.unavailability-form-field").hide(100);
      $("[class*=" + current_type + "]").show(100);
    }

    // make sure it's triggered
    only_show_relevant_fields();
    $('#unavailability_type').change(only_show_relevant_fields);
  });
})(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
