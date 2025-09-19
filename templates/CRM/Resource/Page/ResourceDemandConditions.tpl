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
  <h3 class="header-dark resource-demand-view">{ts}Resource Demand{/ts}</h3>
  <div class="resource-demand-view resource-demand-view-info">
    <table>
      <tr>
        <td>{ts}Label{/ts}</td>
        <td>{$demand_label}</td>
      </tr>
      <tr>
        <td>{ts}Type{/ts}</td>
        <td>{$demand_type_label}</td>
      </tr>
      <tr>
        <td>{ts}Resources{/ts}</td>
        <td><span>{$demand_matching_count} of {$demand_assigned_count} matching ({$demand_resources_count} {ts}required{/ts})</span></td>
      </tr>
    </table>
  </div>

  <h3 class="header-dark resource-demand-view">{ts}Conditions{/ts} <a href="{$condition_create_link}" title="{ts escape='htmlattribute'}Add Resource Condition{/ts}" class="crm-popup medium-popup">[+]</a></h3>
  <div class="resource-demand-view resource-demand-view-conditions">
      {if $conditions}
        <table class="crm-table resource-demand-view resource-demand-view-conditions">
            {foreach from=$conditions item=condition}
              <tr id="condition-{$condition.id}" class="condition">
                <td><i class="crm-i {$condition.icon}" aria-hidden="true"></i></td>
                <td>{$condition.display_name}</td>
                <td class="nowrap">
                    <span>
                      <a href="{$condition.edit_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts escape='htmlattribute'}Edit Condition{/ts}">{ts}Edit{/ts}</a>
                      <a class="action-item crm-hover-button action--resource--demand_condition-delete" data-condition-id="{$condition.id}" title="{ts escape='htmlattribute'}Delete Condition{/ts}" href="#">{ts}Delete{/ts}</a>
                    </span>
                </td>
              </tr>
            {/foreach}
        </table>
      {else}
        <span><i>{ts}no resource conditions specified{/ts}</i></span>
      {/if}
  </div>

  <br/>
{/crmScope}