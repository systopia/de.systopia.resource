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
        <td>{ts}Available{/ts}</td>
        <td>{if $is_available}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
      </tr>
    </table>
  </div>

  <h3 class="header-dark resource-view">{ts}Availability Restrictions{/ts} <a href="{$unavailability_create_link}" title="{ts}Add Availability Restriction{/ts}" class="crm-popup medium-popup">[+]</a></h3>
  <div class="resource-view resource-view-availabilities">
    {if $unavailabilities}
        <table class="crm-table resource-view resource-view-unavailabilities">
            {foreach from=$unavailabilities item=unavailability}
              <tr>
                  <td>{$unavailability.display_name}</td>
                  <td>todo: options</td>
              </tr>
            {/foreach}
        </table>
    {else}
      <span><i>{ts}always available{/ts}</i></span>
    {/if}
  </div>

  <h3 class="header-dark resource-view">{ts}Assignments{/ts}</h3>
  <div class="resource-view resource-view-assignments">
    {if $assignments|count}
      ASDSAD
    {else}
      <i>{ts}no assignments{/ts}</i>
    {/if}
  </div>

  <br/>
{/crmScope}