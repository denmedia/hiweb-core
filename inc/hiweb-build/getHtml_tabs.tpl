<h2 class="nav-tab-wrapper">
    {foreach from=$tabs key=slug item=tab}
        <a href="{$tab.url}" class="nav-tab{if $tab.select}  nav-tab-active{/if}">{$tab.name|tpl}</a>
    {/foreach}
</h2>

{$content}