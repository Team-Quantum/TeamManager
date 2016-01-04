{if isset($maintenance) && $maintenance == 1}
    {include file="pages/maintenance.tpl"}
    {include file='sidebar.tpl'}
{else}
    {include file='head.tpl'}
    {include file="pages/$site_template"}
    {include file='sidebar.tpl'}
    {include file='bottom.tpl'}
{/if}