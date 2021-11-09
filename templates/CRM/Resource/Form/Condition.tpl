{*-------------------------------------------------------+
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
+-------------------------------------------------------*}

{crmScope extensionKey='de.systopia.resource'}

  <div class="crm-section">
    <div class="label">{$form.condition_type.label}</div>
    <div class="content">{$form.condition_type.html}</div>
    <div class="clear"></div>
  </div>

{foreach from=$type_fields item=type_field}
  <div class="crm-section condition-form-field {$type_field}">
    <div class="label">{$form.$type_field.label}</div>
    <div class="content">{$form.$type_field.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}


  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/crmScope}