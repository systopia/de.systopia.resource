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
  <h3>{ts}Information{/ts}</h3>
  Resource Type:
  Resource ID:

  <h3>{ts}Availability{/ts}</h3>
  {if $unavailabilities|count}
    ASDSAD
  {else}
      <i>{ts}generally available{/ts}</i>
      <button>add</button>
  {/if}
  <br/>

  <h3>{ts}Assignments{/ts}</h3>
  {if $assignments|count}
    ASDSAD
  {else}
    <i>{ts}no assignments{/ts}</i>
  {/if}

  <br/>
{/crmScope}