<div class="hiweb-settings-dashboard-head-small hiweb-settings-plugin-head"><h1>{lang}Установка плагинов{/lang}</h1><span>{lang}В этой панеле список плагинов, которые можно скачать и установить прямо из данной панели.{/lang}</span></div>

{if $plugins|count > 0}

<div class="welcome-panel hiweb-core-plugins-groups">
    <div class="alignright">
        <input type="text" placeholder="{lang}Поиск по названию{/lang}" data-fastsearch>{helpPoint}{lang}Введи несколько символов в поле для быстрого поиска плагинов по названию{/lang}{/helpPoint}
    </div>
    <h3>{lang}Группы плагинов{/lang}: {helpPoint}{lang}Выберите группу для более удобного поиска в списке Ваших плагинов{/lang}{/helpPoint}</h3>
    <p data-groups><a href="" data-group-selected>{lang}Все{/lang}</a> </p>
</div>

{/if}

{if $plugins|gettype == 'array' && $plugins|count > 0}
    <div class="tablenav top">

        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">{lang}Выберите массовое действие{/lang}</label><select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">{lang}Действия{/lang}</option>
                <option value="download-active&path">{lang}Скачать и Активировать{/lang}</option>
                <option value="download">{lang}Скачать / Обновить{/lang}</option>
                <option value="active">{lang}Активировать{/lang}</option>
                <option value="deactive">{lang}Деактивировать{/lang}</option>
                <option value="remove">{lang}Удалить{/lang}</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="{lang}Применить{/lang}">
        </div>
        <div class="tablenav-pages one-page">
            <span class="displaying-num">{$plugins|count} {lang}элементов{/lang}</span>
        </div>
    </div>
{/if}

<table class="hiweb-settings-plugins wp-list-table widefat plugins" data-url="{$url}">
    <tbody>
    {if $plugins|count == 0 || $plugins|gettype != 'array'}
    <tr>
        <td>{lang}Репозиторий пуст, или сервер не доступен<br>Введите адрес сервера с плагинами...{/lang}{helpPointImage}plugins-settings-1{/helpPointImage}</td>
    </tr>
    {else}
    <thead>
    <tr>
        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1">{lang}Выделить все{/lang}</label><input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="name" class="manage-column column-name" style=""></th><th scope="col" id="name" class="manage-column column-name" style="">{lang}Плагин{/lang}</th><th scope="col" id="description" class="manage-column column-description" style="">{lang}Описание{/lang}</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1">{lang}Выделить все{/lang}</label><input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="name" class="manage-column column-name" style=""></th><th scope="col" id="name" class="manage-column column-name" style="">{lang}Плагин{/lang}</th><th scope="col" id="description" class="manage-column column-description" style="">{lang}Описание{/lang}</th>
    </tr>
    </tfoot>
    {foreach from=$plugins key=path item=i}
        <tr data-active="{if $i.active}1{else}0{/if}" data-group-hide="{if $group|in_array:$i.groupArr || $group == ''}0{else}1{/if}" data-search-hide="0" {if $i.update}data-update{/if} class="{if $i.exists}active{else}inactive{/if}" data-group="{$i.group}" data-path="{$path}">
            <th scope="row" class="check-column"><label class="screen-reader-text">{lang}Выбрать{/lang} {$i.Name}</label><input type="checkbox" name="checked[]" value="{$path}"></th>
            <td class="plugin-image"></td>
            <td class="plugin-title">
                <strong>{$i.Name}</strong>
                <div><i>{$i.group}</i></div>
                <div class="row-actions visible">
                    {if !$i.exists}
                        <a data-ajax href="{$url}&do=download-active&path={$path}"><b>{lang}Скачать и активировать{/lang}</b></a> | <a data-ajax href="{$url}&do=download&path={$path}">{lang}Скачать{/lang}</a>
                    {elseif $i.update}
                        <a data-ajax href="{$url}&do=download-active&path={$path}"><b>{lang}Обновить{/lang}</b></a> | <a data-ajax href="{$url}&do=deactive&path={$path}">{lang}Деактивировать{/lang}</a> | <a data-ajax href="{$url}&do=deactive-remove&path={$path}">{lang}Деактивировать и удалить{/lang}</a>
                    {elseif $i.active}
                        <a data-ajax href="{$url}&do=deactive&path={$path}"><b>{lang}Деактивировать{/lang}</b></a> | <a data-ajax href="{$url}&do=deactive-remove&path={$path}">{lang}Деактивировать и удалить{/lang}</a>
                    {else}
                        <a data-ajax href="{$url}&do=active&path={$path}"><b>{lang}Активировать{/lang}</b></a> | <a data-ajax href="{$url}&do=remove&path={$path}">{lang}Удалить{/lang}</a>
                    {/if}
                </div>
            </td>
            <td>
                <div class="plugin-description"><p>{if strpos($i.Name,"hiWeb")}{$i.Description}{else}{$i.Description|strip_tags}{/if}</p></div>
                <div class="second plugin-version-author-uri">{lang}Версия{/lang}: {$i.Version} | Размер: {$i.sizeF}</div>
            </td>
        </tr>
    {/foreach}
    {/if}
    </tbody>
</table>

{if $plugins|gettype == 'array' && $plugins|count > 0}
    <div class="tablenav top">

        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">{lang}Выберите массовое действие{/lang}</label><select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">{lang}Действия{/lang}</option>
                <option value="download-active&path">{lang}Скачать и Активировать{/lang}</option>
                <option value="download">{lang}Скачать / Обновить{/lang}</option>
                <option value="active">{lang}Активировать{/lang}</option>
                <option value="deactive">{lang}Деактивировать{/lang}</option>
                <option value="remove">{lang}Удалить{/lang}</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="{lang}Применить{/lang}">
        </div>
        <div class="tablenav-pages one-page">
            <span class="displaying-num">{$plugins|count} {lang}элементов{/lang}</span>
        </div>
    </div>
{/if}
<script>
    jQuery(document).ready(function(){
        hiweb_core_plugins.init();
    });
</script>