@php
    use Illuminate\Support\Str;
@endphp
@foreach ($notifications as $note)
    @php
        $data = json_decode($note->data, true) ?? [];
        $message = $data['message'] ?? ($data['title'] ?? 'Notification');
        $areaName = $note->area_name ?? ($note->area_id ?? 'N/A');
        $isUnread = is_null($note->read_at);

        // Custom Icons & Colors based on dashboard palette
        $icon = 'fas fa-info-circle';
        $iconColor = '#007bff';
        if (Str::contains($note->type, 'Lapsed')) {
            $icon = 'fas fa-exclamation-triangle';
            $iconColor = '#dc3545'; // Red for urgency
        } elseif (Str::contains($note->type, 'NewClient')) {
            $icon = 'fas fa-user-plus';
            $iconColor = '#28a745'; // Green
        } elseif (Str::contains($note->type, 'Payment')) {
            $icon = 'fas fa-receipt';
            $iconColor = '#FF5F00'; // Brand Orange
        }
    @endphp

    <div class="list-group-item list-group-item-action border-0 p-3 notification-item {{ $isUnread ? 'unread' : '' }}"
        data-id="{{ $note->id }}">

        <div class="d-flex align-items-center">
            <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center">
                <i class="{{ $icon }}" style="color: {{ $iconColor }}; font-size: 1.2rem;"></i>
            </div>

            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="message-text {{ $isUnread ? 'font-weight-bold' : '' }}">
                            {{ $message }}
                        </div>
                        <div class="small mt-1 text-muted">
                            <span class="badge badge-light shadow-sm">Area: {{ $areaName }}</span>
                            <span class="mx-1">•</span>
                            <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}
                        </div>
                    </div>

                    <div class="action-area text-right ml-2">
                        @if ($isUnread)
                            <button class="btn btn-xs btn-outline-primary mark-read-btn">
                                Mark Read
                            </button>
                        @else
                            <i class="fas fa-check-circle text-success" title="Read"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
