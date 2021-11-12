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
  // add handler to refresh tab if in resource tab if popup closed
  //  todo: restrict to 'our' popup
  let tab_content_id = cj("#tab_resource").attr('aria-controls');
  if (tab_content_id) {
    cj(document).one('crmPopupFormSuccess', function() {
      cj("#" + tab_content_id).crmSnippet('refresh');
    });
  }
});
