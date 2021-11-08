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
  {if $contact_with_resource_count gt 0}
    <div id="help">{ts 1=$contact_with_resource_count}Remark: %1 of the selected contacts are already in use as a resource.{/ts}</div>
  {/if}
  <div class="crm-section">
      <div class="label">{$form.resource_type_id.label}{help id="id-resource-type-id" title=$form.resource_type_id.label}</div>
      <div class="content">{$form.resource_type_id.html}</div>
      <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.label_pattern.label}{help id="id-label-pattern" title=$form.label_pattern.label}</div>
    <div class="content">{$form.label_pattern.html}</div>
    <div class="clear"></div>
  </div>

  {* FOOTER *}
  <br>
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/crmScope}
</script>