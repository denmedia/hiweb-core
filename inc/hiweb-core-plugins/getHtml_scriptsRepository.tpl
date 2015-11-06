<div class="hiweb-settings-dashboard-head-small hiweb-settings-plugin-head"><h1>{lang}Установка скриптов и ассетов{/lang}</h1><span>{lang}В этой панеле список скриптов и ассетов, которые можно скачать и установить прямо из данной панели.{/lang}</span></div>

{if $scripts|count > 0}
<div class="welcome-panel hiweb-core-plugins-groups">
    <div class="alignright">
        <input type="text" placeholder="{lang}Поиск по названию{/lang}" data-fastsearch>
    </div>
    <h3>{lang}Группы скриптов{/lang}:</h3>
    <p data-groups><a href="" data-group-selected>{lang}Все{/lang}</a> </p>
</div>
{/if}

<table class="hiweb-settings-plugins wp-list-table widefat plugins" data-url="{$url}">
    <tbody>
    {if $scripts|count == 0 || $scripts|gettype != 'array'}
    <tr>
        <td>{lang}Репозиторий пуст, или сервер не доступен<br>Введите адрес сервера с плагинами...{/lang}{helpPointImage}plugins-settings-1{/helpPointImage}</td>
    </tr>
    {else}
    <thead>
    <tr>
        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""></th><th scope="col" class="manage-column"></th><th scope="col" id="name" class="manage-column column-name" style="">{lang}Скрипты{/lang}</th><th scope="col" id="description" class="manage-column column-description" style="">{lang}Описание{/lang}</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""></th><th scope="col" class="manage-column"></th><th scope="col" id="name" class="manage-column column-name" style="">{lang}Скрипты{/lang}</th><th scope="col" id="description" class="manage-column column-description" style="">{lang}Описание{/lang}</th>
    </tr>
    </tfoot>
    {foreach from=$scripts key=path item=i}
        <tr class="{if $i.exists}active{else}inactive{/if}" data-path="{$path}" data-group="{$i.group}" data-group-hide="0" data-search-hide="0" {if $i.update}data-update{/if}>
            <th scope="row" class="check-column"></th>
            <td class="plugin-image"></td>
            <td class="plugin-title">
                <strong>{$i.name}</strong>
                <div class="row-actions visible">
                    {if !$i.exists}
                        <a data-ajax href="{$url}&do=download&path={$path}"><b>{lang}Скачать{/lang}</b></a>
                    {elseif $i.update}
                        <a data-ajax href="{$url}&do=download&path={$path}">{lang}Обновить{/lang}</a> | <a data-ajax href="{$url}&do=remove&path={$path}">{lang}Удалить{/lang}</a>
                    {else}
                        <a data-ajax href="{$url}&do=remove&path={$path}">{lang}Удалить{/lang}</a>
                    {/if}
                </div>
            </td>
            <td>
                <div class="plugin-description"><p>{if strpos($i.name,"hiWeb")}{$i.desc}{else}{$i.desc|strip_tags}{/if}</p></div>
                <div class="second plugin-version-author-uri">{lang}Версия{/lang}: {$i.version} | {lang}Размер{/lang}: {$i.sizeF}</div>
                <div><code>{'<?php hiweb()->file()->asset("'|escape}{$i.slug}{'"); ?>'|escape}</code></div>
            </td>
        </tr>
    {/foreach}
    {/if}
    </tbody>
</table>

<script>
    jQuery(document).ready(function(){
        hiweb_core_plugins.init();
    });
</script>