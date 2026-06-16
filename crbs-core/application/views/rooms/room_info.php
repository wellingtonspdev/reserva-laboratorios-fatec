<?php
// Campos fixos e campos customizados separados
$fixed_names   = ['group', 'location', 'teacher', 'notes'];
$custom_fields = array_filter($room_info, function($r) use ($fixed_names) {
    return !in_array($r['name'], $fixed_names);
});

// Retorna o valor de um campo fixo pelo nome
$get_fixed = function ($key) use ($room_info) {
    foreach ($room_info as $row) {
        if ($row['name'] === $key) return $row['value'];
    }
    return null;
};

$render_value = function ($field) {
    if (!isset($field['value']) || $field['value'] === null || $field['value'] === '') {
        return '';
    }

    if (!empty($field['value_html'])) {
        return $field['value'];
    }

    return nl2br(html_escape($field['value']));
};

$labels = [
    'location' => lang('room.field.location'),
    'teacher'  => lang('room.field.teacher'),
    'notes'    => lang('room.field.notes'),
];

$svg_building = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>';
$svg_pin      = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
$svg_user     = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
$svg_notes    = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
$svg_gear     = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
$svg_close    = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';

$icons_by_key = ['location' => $svg_pin, 'teacher' => $svg_user, 'notes' => $svg_notes];
$is_embedded = !empty($room_info_embedded);
$return_uri = !empty($return_uri) ? $return_uri : 'bookings';
$close_label = html_escape(lang('room.info.close'));
$back_label = html_escape(lang('app.action.back'));
$back_button = anchor('#', $svg_close . "<span class=\"cps-sr-only\">{$close_label}</span>", [
    'class' => 'cps-room-info-close',
    'up-dismiss' => '',
    'aria-label' => lang('room.info.close'),
    'title' => lang('app.action.close'),
]);
$return_button = anchor($return_uri, '&larr; ' . $back_label, [
    'class' => 'inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100',
    'aria-label' => lang('room.info.back'),
]);
?>

<div class="room-info w-full p-5 text-gray-800" style="max-width:640px; margin:0 auto;">
    <?php if (!$is_embedded): ?>
        <div class="mb-4">
            <?= $return_button ?>
        </div>
    <?php endif; ?>

    <?php /* ── Cabeçalho ── */ ?>
    <div class="flex items-center justify-between gap-3 pb-4 border-b border-gray-200 mb-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-white" style="background-color:#B20000;">
                <?= $svg_building ?>
            </div>
            <div>
                <?php if ($is_embedded): ?>
                    <h2 class="text-lg font-bold leading-tight text-gray-900 m-0"><?= html_escape($room->name) ?></h2>
                <?php endif; ?>
                <?php if ($get_fixed('group')): ?>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5 m-0"><?= html_escape($get_fixed('group')) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 flex-shrink-0">
            <?php if ($get_fixed('location')): ?>
                <div class="inline-flex items-center gap-1 text-xs font-semibold rounded-full px-3 py-1 flex-shrink-0 text-white" style="background-color:#B20000;">
                    <?= $svg_pin ?>
                    <span><?= html_escape($get_fixed('location')) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($is_embedded): ?>
                <?= $back_button ?>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ── Foto ── */ ?>
    <?php if (!empty($photo_url)): ?>
        <div class="mb-5 rounded-xl overflow-hidden border border-gray-200" style="aspect-ratio:16/9; background:#f5f5f5;">
            <img src="<?= $photo_url ?>" alt="<?= html_escape(sprintf(lang('room.photo.alt'), $room->name)) ?>" class="w-full h-full object-cover">
        </div>
    <?php endif; ?>

    <?php /* ── Informações principais ── */ ?>
    <?php
    $has_main = $get_fixed('location') || $get_fixed('teacher') || $get_fixed('notes');
    if ($has_main):
    ?>
        <div class="grid grid-cols-1 gap-3 mb-5">
            <?php foreach (['location', 'teacher', 'notes'] as $key):
                $val = $get_fixed($key);
                if (!$val) continue;
            ?>
                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200" style="background:#fafafa;">
                    <div class="mt-0.5 flex-shrink-0" style="color:#B20000;">
                        <?= $icons_by_key[$key] ?>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 m-0 mb-1"><?= $labels[$key] ?></p>
                        <p class="text-sm text-gray-800 leading-relaxed m-0"><?= nl2br(html_escape($val)) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php /* ── Campos customizados ── */ ?>
    <?php if (!empty($custom_fields)): ?>
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 pt-4 border-t border-gray-200">
            <div style="color:#B20000;"><?= $svg_gear ?></div>
            <span><?= html_escape(lang('room.info.specifications')) ?></span>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <?php foreach ($custom_fields as $field): ?>
                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200" style="background:#fafafa;">
                    <div class="mt-0.5 flex-shrink-0" style="color:#B20000;"><?= $svg_gear ?></div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 m-0 mb-1"><?= html_escape($field['label']) ?></p>
                        <div class="text-sm text-gray-800 leading-relaxed m-0">
                            <?php if ($field['value'] !== null && $field['value'] !== ''): ?>
                                <?= $render_value($field) ?>
                            <?php else: ?>
                                <span class="text-gray-300 italic"><?= html_escape(lang('room.info.empty_value')) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($room_info) || (!$has_main && empty($custom_fields))): ?>
        <div class="py-10 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full mb-3" style="background:#f5f5f5;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <p class="text-sm text-gray-400 m-0"><?= html_escape(lang('room.info.no_details')) ?></p>
        </div>
    <?php endif; ?>

</div>
