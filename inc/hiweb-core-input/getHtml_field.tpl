{$display = true}
{$displayRule = ''}
{if $field.display|is_array}
    {if $field.display|count > 0}{$display = false}{$displayRule = ' data-hiweb-input-display-rule="'|cat:{$field.display|json_encode|escape}|cat:'"'}{/if}
    {foreach from=$field.display item=rule}
        {if isset($fields[{$rule.id}]) && isset($fields[{$rule.id}]['value'])}
            {$ruleValue = $fields[{$rule.id}]['value']}
            {if
            ($rule.operator == '==' && $rule.value == $ruleValue) ||
            ($rule.operator == '!=' && $rule.value != $ruleValue) ||
            ($rule.operator == '<' && $rule.value < $ruleValue) ||
            ($rule.operator == '<=' && $rule.value <= $ruleValue) ||
            ($rule.operator == '>' && $rule.value > $ruleValue) ||
            ($rule.operator == '>=' && $rule.value >= $ruleValue)
            }
                {$display = true}
            {/if}
        {/if}
    {/foreach}
{/if}

<div{if !$display} style="display: none;"{/if}{$displayRule}>
    <b>
    {if isset($field.name)}{$field.name|tpl}{/if}
    </b>
    {if isset($field.html)}{$field.html}{/if}
    {if $field.label != ''}<label for="{$id}">{$field.label|tpl}</label>{/if}
    <p>{$field.description|tpl}</p>
</div>