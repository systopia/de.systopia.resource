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
  <h3 class="header-dark resource-view">{ts}Resource Information{/ts}</h3>
  <div class="resource-view resource-view-info">
    <table>
      <tr>
        <td>{ts}Label{/ts}</td>
        <td>{$resource_label}</td>
      </tr>
      <tr>
        <td>{ts}Type{/ts}</td>
        <td>{$resource_type_label}</td>
      </tr>
      <tr>
        <td>{ts}Available Now{/ts}</td>
        <td>{if $is_available}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
      </tr>
    </table>
  </div>

  <h3 class="header-dark resource-view">{ts}Availability Restrictions{/ts} <a href="{$unavailability_create_link}" title="{ts}Add Availability Restriction{/ts}" class="crm-popup medium-popup">[+]</a></h3>
  <div class="resource-view resource-view-availabilities">
    {if $unavailabilities}
        <table class="crm-table resource-view resource-view-unavailabilities">
            {foreach from=$unavailabilities item=unavailability}
              <tr id="unavailability-{$unavailability.id}" class="unavailability {if $unavailability.active_now}unavailability-active-now{/if}">
                  <td>{$unavailability.display_name}</td>
                  <td class="nowrap">
                    <span>
                      <a href="{$unavailability.edit_link}" class="action-item crm-hover-button crm-popup medium-popup" title="{ts}Edit Unavailability{/ts}">{ts}Edit{/ts}</a>
                      <a class="action-item crm-hover-button" onclick="delete_unavailability({$unavailability.id});" title="{ts}Delete Unavailability{/ts}">{ts}Delete{/ts}</a>
                    </span>
                  </td>
              </tr>
            {/foreach}
        </table>
    {else}
      <span><i>{ts}always available{/ts}</i></span>
    {/if}
  </div>

  <h3 class="header-dark resource-view">{ts}Assignments [TODO]{/ts}</h3>
  <div class="resource-view resource-view-assignments">
    {if $assignments|count}
      <table class="crm-table resource-view resource-view-unavailabilities">
          {foreach from=$assignments item=assignment}
            <tr id="assignment-{$assignment.id}" class="assignment">
              <td>Resource Demand {$assignment.resource_demand_id}, Status {$assignment.status}</td>
              <td class="nowrap">
                    <span>
                      <a class="action-item crm-hover-button" onclick="delete_assignment({$assignment.id});" title="{ts}Unassign{/ts}">{ts}Unassign{/ts}</a>
                    </span>
              </td>
            </tr>
          {/foreach}
      </table>
    {else}
      <i>{ts}no assignments{/ts}</i>
    {/if}
  </div>

  <br/>
{/crmScope}