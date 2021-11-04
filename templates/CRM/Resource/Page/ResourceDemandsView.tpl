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
  <h3 class="header-dark resource-demand-view">{ts 1=0 2=100}Required Resources (%1 of %2){/ts}</h3>
  <div class="resource-demand-view resource-demand-view-availabilities">
    {if $resource_demands}
        <table class="crm-table resource-demand-view resource-demand-view-unavailabilities">
            {foreach from=$resource_demands item=resource_demand}
              <tr id="resource_demand-{$resource_demand.id}" class="resource_demand {if $resource_demand.active_now}resource_demand-active-now{/if}">
                  <td>{$resource_demand.display_name}</td>
                  <td class="nowrap">
                    <span>
                      <a href="{$resource_demand.edit_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts}Edit Unavailability{/ts}">{ts}Edit{/ts}</a>
                      <a class="action-item crm-hover-button" onclick="delete_resource_demand({$resource_demand.id});" title="{ts}Delete Unavailability{/ts}">{ts}Delete{/ts}</a>
                    </span>
                  </td>
              </tr>
            {/foreach}
        </table>
    {/if}
  </div>

  <!-- add more button -->
  <a href="{$create_resource_requirement_link}" title="{ts}Add More Resource Requirement{/ts}" class="crm-popup medium-popup">
    <button class="crm-button crm-button-type-add" value="1" accesskey="A"><i aria-hidden="true" class="crm-i fa-plus"></i>Add More</button>
  </a>
  <br/>
{/crmScope}