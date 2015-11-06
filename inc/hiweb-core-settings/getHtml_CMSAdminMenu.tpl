<input type="hidden" id="hiweb-core-settings-adminmenu-currentuser" value="{$user_login}">
<table class="hiweb-core-settings-adminmenu-table">
    <tbody>
    <tr>
        <th>Название пункта меню</th>
        <th>Настройка для данного пункта меню</th>
    </tr>
    {foreach from=$table item=i key=id}
        <tr data-line data-menu-slug="{$id|escape}">
            <td data-adminmenu-name>
                <p><b>{$i.name}</b></p>
                <p><input placeholder="Переименовать" type="text" data-line-name /></p>
            </td>
            <td>
                <p>
                    {$i.mode}
                    Роли:{$i.roles}
                    Пользователи: {$i.users}</p>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
<p>
    <button class="button button-primary" id="hiweb-core-settings-adminmenu-submit">{lang}Сохранить изменения{/lang}</button>
<div id="hiweb-core-settings-adminmenu-done">{lang}Все изменения сохранены!{/lang}</div>
</p>
