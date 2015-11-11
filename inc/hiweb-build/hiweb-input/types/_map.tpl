<div class="hiweb-input-field">
    <input type="hidden" data-type="map" id="{$id}" value="{','|implode:$field.value}" {$field.tagsHtml}>
    <input type="text" id="{$id}-lat" name="{$id}[]" value="{$field.value[0]}">
    <input type="text" id="{$id}-long" name="{$id}[]" value="{$field.value[1]}">
    <div id="{$id}-yamap" data-id="{$id}" style="height: 300px;"></div>
    <em>Для установки координат, кликните в нужном для Вас месте, либо перетащите существующий маркер.</em>
</div>