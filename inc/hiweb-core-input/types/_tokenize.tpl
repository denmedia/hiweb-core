<div class="hiweb-input-field">
    <select name="{$id}[]" id="{$id}" {$field.tagsHtml} class="tokenize" multiple="multiple">
        {foreach from=$field.options key=optionKey item=optionValue}
            <option value="{$optionKey|escape}" {if is_array($field.value)}{if $optionKey|in_array:$field.value} selected="selected"{/if}{/if}>{$optionValue}</option>
        {/foreach}
    </select>
</div>