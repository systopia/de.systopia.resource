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
  <h3 class="demand-view demand-assignment-status">
      {ts 1=$assigned_count 2=$required_count}There are currently %1 of %2 required resources assigned.{/ts}
  </h3>

  <div class="demand-view demand-matching-demands">
      {if $resources}
        <table class="crm-table demand-demand-view demand-demand-view-unavailabilities">
          <thead>
          <tr>
            <th>{ts}Name{/ts}</th>
            <th>{ts}Entity ID{/ts}</th>
            <th>{ts}Meets Conditions{/ts}</th>
            <th>{ts}Unassign{/ts}&nbsp;<span class="demand resource-all">[{ts}all{/ts}]</span></th>
          </tr>
          </thead>
          <tbody>
          {foreach from=$resources item=resource}
            <tr id="demand-{$resource.id}" class="demand resource-view {if $resource.meets_demand}{ts}resource-demand-met{/ts}{else}{ts}resource-demand-not-met{/ts}{/if}">
                {assign var="field_name" value=$resource.field_name}
              <td>{$resource.label} [{$resource.id}]</td>
              <td>{$resource.entity_id}</td>
              <td>{if $resource.meets_demand}{ts}yes{/ts}{else}{ts}no{/ts}{/if}</td>
              <td>{$form.$field_name.html}</td>
            </tr>
          {/foreach}
          </tbody>
        </table>
      {else}
        <div class="demand demand-missing">{ts}You have removed all resources from this demand.{/ts}</div>
      {/if}
  </div>

  <h3 style="display: none;" class="demand demand-too-many">
      {ts}You have selected more demands than you need.{/ts}
  </h3>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

{/crmScope}
