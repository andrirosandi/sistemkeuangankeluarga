@props([
    'type' => 'edit', // edit, delete, reset
    'onclick' => '',
    'title' => '',
    'color' => null
])

@php
    $config = [
        'edit' => ['icon' => 'pencil', 'color' => 'primary', 'default_title' => 'Edit'],
        'delete' => ['icon' => 'trash', 'color' => 'danger', 'default_title' => 'Hapus'],
        'reset' => ['icon' => 'key', 'color' => 'warning', 'default_title' => 'Reset Password'],
    ][$type] ?? ['icon' => 'help', 'color' => 'secondary', 'default_title' => 'Aksi'];

    $finalColor = $color ?? $config['color'];
    $finalTitle = $title ?: $config['default_title'];
@endphp

<button class="btn btn-icon btn-sm btn-ghost-{{ $finalColor }} rounded-2"
        onclick="{!! $onclick !!}"
        title="{{ $finalTitle }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
    <i class="ti ti-{{ $config['icon'] }}"></i>
</button>
