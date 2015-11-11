<div class="hiweb-input-field" data-type="taxonomies_posts" data-id="{$id}" data-post-type="{$field['post_type']|json_encode|escape}">
    <div data-term-list>
        <p><em>Двойной клик по категории установит все пункты, относящиеся к ней:</em></p>
        <ul data-type="term-select" data-taxonomies="{$field['post_taxonomy']|json_encode|escape}">
            <li data-term="" data-term-select="1">Все</li>
            {foreach from=$terms key=termId item=term}
                <li data-taxonomy="{$term->taxonomy}" data-term="{$termId}">{$term->name}</li>
            {/foreach}
            <li data-clear>Очистить все</li>
        </ul>
    </div>
    <div data-post-list>
        {$input}
    </div>
</div>