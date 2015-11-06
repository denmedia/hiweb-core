<div class="hiweb-input-field">
    <input
            name="{$id}"
            id="{$id}"
            type="{$field.type}"
            {if $field.value != ''}value="{$field.value|escape}"{/if}
            title="{$field.name}"
            class="regular-text hiweb-input"
            {$tagsHtml}/>
    <i class="fa fa-text-width"></i>
</div>