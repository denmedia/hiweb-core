<input type="hidden" id="hiweb-core-settings-adminmenu-currentuser" value="{$user_login}">
<input type="hidden" id="hiweb-core-settings-adminmenu-currentrole" value="{$role}">

<p>
    <button class="button button-primary" id="hiweb-core-settings-adminmenu-submit">{lang}Сохранить изменения{/lang}</button>
<div id="hiweb-core-settings-adminmenu-done">{lang}Все изменения сохранены!{/lang}</div>
</p>

<table class="hiweb-core-settings-adminmenu-table">
    <tbody>
    <tr>
        <th>Название пункта меню</th>
        <th>Настройка видимсти</th>
    </tr>
    {foreach from=$table item=i key=id}
        <tr data-line data-menu-slug="{$id|escape}">
            <td data-adminmenu-name>
                <h3 data-real-name>{$i.name}</h3>
                {$i.rename}
            </td>
            <td>
                <p>
                    {$i.mode}
                    {$i.roles}
                    {$i.users}</p>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>



<p>
    <button class="button button-primary" id="hiweb-core-settings-adminmenu-submit">{lang}Сохранить изменения{/lang}</button>
<div id="hiweb-core-settings-adminmenu-done-2">{lang}Все изменения сохранены!{/lang}</div>
</p>
