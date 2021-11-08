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
  <h3 class="header-dark resource-view">{ts 1=$demand_label}Assign "%1" Resources{/ts}</h3>

  <div class="resource-view resource-assignment-status">
    {if $assigned_now}
        {if $assigned_missing}
            {ts 1=$assigned_now 2=$assigned_requested 3=$demand_label 4=$assigned_missing}There are already %1 of %2 "%3" assigned, please assign %4 more.{/ts}
        {else}
            {ts 1=$demand_label}There are already enough "%1" assigned, but if you want you can still assign more.{/ts}
        {/if}
    {else}
      {ts 1=$demand_label 2=$assigned_missing}Please assign %2 "%1".{/ts}
    {/if}
    {ts}Here's a list of some available resources currently matching the requirements:{/ts}
  </div>

  <div class="resource-view resource-matching-resources">
      {if $candidates}
        <table class="crm-table resource-resource-view resource-resource-view-unavailabilities">
          <thead>
          <tr>
            <th>{ts}Assign{/ts}</th>
            <th>{ts}Name{/ts}</th>
            <th>{ts}Entity ID{/ts}</th>
          </tr>
          </thead>
            {foreach from=$candidates item=candidate}
              <tr id="resource-{$candidate.id}" class="resource resource-view">
                {assign var="field_name" value=$candidate.field_name}
                <td>{$form.$field_name.html}</td>
                <td>{$candidate.label} [{$candidate.id}]</td>
                <td>{$candidate.entity_id}</td>
              </tr>
            {/foreach}
        </table>
      {/if}
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

{/crmScope}