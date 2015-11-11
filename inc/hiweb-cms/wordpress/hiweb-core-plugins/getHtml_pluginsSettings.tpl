<div class="hiweb-settings-dashboard-head-small hiweb-settings-plugin-head"><h1>{lang}Установка плагинов{/lang} → {lang}настройки{/lang}</h1><span>{lang}В этой панеле настраивается репозиторий плагинов.{/lang}</span></div>

<form method="post" action="options.php">

    <table class="form-table">
        {foreach from=$fields key=id item=a}
            <tr valign="top">
                <th>{$a.name|tpl}</th>
                <td>
                    <input name="{$id}" id="{$id}" type="{$a.type}" {if $a.val != ''}value="{$a.val}"{/if} title="{$a.name|tpl}"/>
                    <label for="{$id}"><b></b></label>
                </td>
                <td>{$a.desc|tpl}</td>
            </tr>
        {/foreach}
    </table>

    {$wp_nonce}
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="{','|implode:$ids}" />

    <p class="submit">
        <input type="submit" class="button-primary" value="{$savechanges}" />
    </p>
</form>