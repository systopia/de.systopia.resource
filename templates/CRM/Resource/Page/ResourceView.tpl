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
    <h3 class="header-dark resource-view">{ts}Resource Information{/ts}</h3>
    <div class="crm-container section-shown resource-view resource-view-info">
      <table class="no-border">
        <tr>
          <th>{ts}Label{/ts}</th>
          <td>{$resource_label}</td>
        </tr>
        <tr>
          <th>{ts}Type{/ts}</th>
          <td>{$resource_type_label}</td>
        </tr>
        <tr>
          <th>{ts}Available Now{/ts}</th>
          <td>{if $is_available}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
        </tr>
      </table>
    </div>

    <h3 class="header-dark resource-view">{ts}Availability Restrictions{/ts} <a href="{$unavailability_create_link}" title="{ts}Add Availability Restriction{/ts}" class="crm-popup medium-popup">[+]</a></h3>
    <div class="crm-container section-shown resource-view resource-view-availabilities">
          <table class="crm-table row-highlight resource-view resource-view-unavailabilities">
              {if $unavailabilities}
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
              {else}
                <tr>
                  <td colspans="2"><em>{ts}Always available{/ts}</em></td>
                </tr>
              {/if}
          </table>
    </div>

    <h3 class="header-dark resource-view">{ts}Assignments{/ts} <a href="{$assignment_create_link}" title="{ts}Assign to Other Demands{/ts}" class="crm-popup small-popup">[+]</a></h3>
    <div class="crm-container section-shown resource-view resource-view-assignments">
          <table class="crm-table row-highlight resource-view resource-view-unavailabilities">
              {if $assignments|count}
              {foreach from=$assignments item=assignment}
                <tr id="assignment-{$assignment_id}" class="assignment">
                  <td>{$assignment.name} @ {$assignment.entity_label}</td>
                  <td>{$assignment.time}</td>
                  <td>{$assignment.status}</td>
                  <td class="nowrap">
                    <span>
                      <a class="action-item crm-hover-button" onclick="delete_assignment({$assignment.assignment_id});" title="{ts}Unassign{/ts}">{ts}Unassign{/ts}</a>
                    </span>
                  </td>
                </tr>
              {/foreach}
              {else}
                <tr><td colspan="4"><em>{ts}No assignments{/ts}</em></td></tr>
              {/if}
          </table>
    </div>
  </div>
{/crmScope}