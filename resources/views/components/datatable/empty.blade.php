@props([
    'title' => 'Belum ada data',
    'icon' => 'ti-file-off',
    'subtitle' => null,
    'actionText' => null,
    'actionUrl' => null,
    'colspan' => 7 // Default colspan for full width coverage
])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5">
        <div class="empty">
            <div class="empty-icon text-secondary mb-3">
                <i class="ti {{ $icon }} icon-lg" style="font-size: 3rem;"></i>
            </div>
            <p class="empty-title h3">{{ $title }}</p>
            @if($subtitle)
                <p class="empty-subtitle text-muted">
                    {{ $subtitle }}
                </p>
            @endif
            @if($actionText && $actionUrl)
                <div class="empty-action mt-4">
                    <a href="{{ $actionUrl }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i> {{ $actionText }}
                    </a>
                </div>
            @endif
        </div>
    </td>
</tr>
