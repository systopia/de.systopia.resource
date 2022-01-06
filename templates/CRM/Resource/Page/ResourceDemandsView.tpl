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
  <div class="crm-block crm-content-block">
    <h3 class="header-dark resource-demand-view">{ts 1=$demands_met_count 2=$demand_count}Required Resources (%1 of %2 ok){/ts}</h3>
    <div class="resource-demand-view resource-demand-view-availabilities">
      {if $resource_demand_data}
          <table class="crm-table row-highlight resource-demand-view resource-demand-view-unavailabilities">
              <thead>
                <tr>
                  <th>{ts}Resource{/ts}</th>
                  <th>{ts}Type{/ts}</th>
                  <th>{ts}Assigned{/ts}</th>
                  <th>{ts}Fulfilled{/ts}</th>
                  <th>{ts}Conditions{/ts}</th>
                  <th>{ts}Actions{/ts}</th>
                </tr>
              </thead>
              {foreach from=$resource_demand_data item=resource_demand}
                <tr id="resource_demand-{$resource_demand.id}" class="resource_demand {if $resource_demand.is_met}resource_demand-met{/if}">
                    <td>
                        {$resource_demand.label}
                        {if $resource_demand.is_eternal}&nbsp;<span title="{$eternal_warning}" class="resource_demand resource_demand-infinite">&#9854;</span>{/if}
                    </td>
                    <td>{$resource_demand.type_label}</td>
                    <td>
                        {if $resource_demand.assignment_count}
                          <a href="{$resource_demand.unassign_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts}Manage Assignments{/ts}">{$resource_demand.assignment_count} / {$resource_demand.count}</a>
                        {else}
                          <span title="{ts}No Assignments{/ts}">{$resource_demand.assignment_count} / {$resource_demand.count}</span>
                        {/if}
                        {if $resource_demand.assignment_count gt $resource_demand.count}<i aria-hidden="true" class="crm-i fa-angle-double-up" title="{ts}more resources assigned than required{/ts}">{/if}
                    </td>
                    <td>{$resource_demand.fulfilled_count} / {$resource_demand.count} {if $resource_demand.fulfilled_count gt $resource_demand.count}<i aria-hidden="true" class="crm-i fa-angle-double-up" title="{ts}more resources assigned than required{/ts}">{/if}</td>
                    <td class="nowrap">
                        <span>
                          <a href="{$resource_demand.conditions_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts}Edit Conditions{/ts}">{ts 1=$resource_demand.condition_count}Modify (%1){/ts}</a>
                        </span>
                    </td>
                    <td class="nowrap">
                      <span>
                        <a href="{$resource_demand.assign_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts}Assign New Resources{/ts}">{ts}Assign{/ts}</a>
                        <a href="{$resource_demand.edit_link}" class="action-item crm-hover-button crm-popup small-popup" title="{ts}Edit Resource Demand{/ts}">{ts}Edit{/ts}</a>
                        <a class="action-item crm-hover-button" onclick="delete_resource_demand({$resource_demand.id});" title="{ts}Delete Resource Demand{/ts}">{ts}Delete{/ts}</a>
                      </span>
                    </td>
                </tr>
              {/foreach}
          </table>
      {/if}
    </div>

    <div class="action-link">
        {crmButton p="civicrm/resource/demand/create" q="reset=1&entity_table=$entity_table&entity_id=$entity_id" id="newsResourceDemand" class="crm-popup small-popup" icon="plus-circle"}{ts}Add Resource Demand{/ts}{/crmButton}
    </div>

  </div>
{/crmScope}