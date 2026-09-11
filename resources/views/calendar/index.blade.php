@extends('layouts.app')

@section('title', 'Calendar')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
@endpush

@section('content')
    @php($title = 'Calendar')

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Deadlines</p>
            <h1 class="page-title h3 mb-0">Calendar</h1>
        </div>
        <div class="d-flex gap-2">
            <span class="badge badge-dev">DEV</span>
            <span class="badge badge-client">CLIENT</span>
        </div>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                },
                events: @json(route('calendar.events')),
                eventClick(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },
                eventDidMount(info) {
                    const props = info.event.extendedProps;
                    info.el.title = [
                        info.event.title,
                        props.deadline_type,
                        props.deadline_time,
                        props.project,
                    ].filter(Boolean).join(' · ');
                },
            });

            calendar.render();
        });
    </script>
@endpush
