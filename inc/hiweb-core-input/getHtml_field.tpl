{$display = true}
{$displayRule = ''}
{if $fieldArr.display|is_array}
    {if $fieldArr.display|count > 0}{$display = false}{$displayRule = ' data-hiweb-input-display-rule="'|cat:{$fieldArr.display|json_encode|escape}|cat:'"'}{/if}
    {foreach from=$fieldArr.display item=rule}
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

<span{if !$display} style="display: none;"{/if}{$displayRule}>{$field}</span>