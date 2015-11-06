<div class="hiweb-input-field limit">
    <select name="{$id}" id="{$id}" class="onceselect" {$tagsHtml}>
        <option label="{if $field.placeholder == ''}выберите вариант{else}{$field.placeholder}{/if}" value="">{if $field.placeholder == ''}выберите вариант{else}{$field.placeholder}{/if}</option>
        {foreach from=$field.options key=optionKey item=optionValue}
            <option value="{$optionKey|escape}"{if $optionKey==$field.value} selected="selected"{/if}>{$optionValue}</option>
        {/foreach}
    </select>
</div>