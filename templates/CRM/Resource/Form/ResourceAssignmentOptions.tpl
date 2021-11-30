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
  {if $candidates}
  <h3 class="resource-view resource-assignment-status">
    {ts}Here's a list of demands that this resource would be suitable for:{/ts}
  </h3>
  {/if}

  <div class="demand-view resource-matching-demands">
      {if $candidates}
        <table class="crm-table resource-demand-view">
          <thead>
          <tr>
            <th>{ts}Resource Demand{/ts}</th>
            <th>{ts}Current{/ts}</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          {foreach from=$candidates item=candidate}
            <tr id="demand-{$candidate.id}" class="resource-demand {if $candidate.current_count lt $candidate.required_count}resource-demand-assignment-good{else}resource-demand-assignment-full{/if}">
              <td>{$candidate.label} @ {$candidate.demand_label} [{$candidate.id}]</td>
              <td class="resource-demand-assignment">
                  {$candidate.current_count} / {$candidate.required_count}
              </td>
              <td>
                <button type="button" class="button crm-hover-button resource-demand resource-demand-assign" data-demand-id="{$candidate.id}" data-resource-id="{$resource_id}">{ts}Assign{/ts}</button>
              </td>
            </tr>
          {/foreach}
          </tbody>
        </table>
      {else}
        <div class="resource resource-missing">{ts}Sorry, there's currently no more open resource demands matching this resource.{/ts}</div>
      {/if}
  </div>

  <h3 style="display: none;" class="resource resource-too-many">
      {ts}You have selected more resources than you need.{/ts}
  </h3>

{*  <div class="crm-submit-buttons">*}
{*      {include file="CRM/common/formButtons.tpl" location="bottom"}*}
{*  </div>*}

{/crmScope}
