<div class="wrap">

    {if !empty($title)}<h1>{$title}</h1>{/if}

    <form method="post" action="options.php">
        {$wp_nonce}
        <table class="form-table">

            {foreach from=$fields key=id item=a}
                {if !$a|is_array}
                    <tr>
                        <th colspan="2"><h3>{$a|tpl}</h3></th>
                    </tr>
                {else}
                    {$tags = array()}
                    {if $a.tags|is_array}
                        {foreach from=$a.tags key=tagName item=tagValue}
                            {$tags[] = "$tagName='$tagValue'"}
                        {/foreach}
                    {/if}


                    {$display = true}
                    {$displayRule = ''}
                    {if $a.display|is_array}
                        {if $a.display|count > 0}{$display = false}{$displayRule = ' data-hiweb-input-display-rule="'|cat:{$a.display|json_encode|escape}|cat:'"'}{/if}
                        {foreach from=$a.display item=rule}
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


                    <tr{if !$display} style="display: none;"{/if}{$displayRule}>
                        <th>{$a.name|tpl}</th>
                        <td valign="top">
                            {if isset($a.html)}{$a.html}{/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}

        </table>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="{','|implode:$ids}" />


        <p class="submit">
            <input type="submit" class="button-primary" value="{$savechanges}" />
        </p>


    </form>


</div>