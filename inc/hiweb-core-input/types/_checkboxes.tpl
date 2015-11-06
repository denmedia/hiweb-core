{foreach from=$field.options item=i key=k}
    <div class="hiweb-input-field" data-type="checkboxes">
        <div class="hiweb-input-boolean" data-text="false" data-color="light-blue" data-radius="true">
            <input id="{$id}-{$k}" type="checkbox" value="{$k}" name="{$id}[{$k}]" {if isset($field.value[$k]) != ''}checked="checked"{/if} {$tagsHtml}>
            <label for="{$id}-{$k}"><i data-before-check="&#xf10c;" data-after-check="&#xf058;"></i>{$i}</label>
        </div>
    </div>
{/foreach}
