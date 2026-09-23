@extends('layouts.app')

@section('title', 'Calendar')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
@endpush

@section('content')
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
        <div class="card-body p-2 p-md-3">
            <div class="calendar-shell">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendarEl = document.getElementById('calendar');

            if (!calendarEl || !window.FullCalendar) {
                return;
            }

            const mobileQuery = window.matchMedia('(max-width: 767.98px)');

            const applyLayout = () => {
                const mobile = mobileQuery.matches;

                calendar.setOption('headerToolbar', mobile
                    ? { left: 'prev,next', center: 'title', right: 'today' }
                    : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' });
                calendar.setOption('footerToolbar', mobile
                    ? { center: 'listWeek,dayGridMonth,timeGridDay' }
                    : false);
                calendar.setOption('dayMaxEventRows', mobile ? 2 : true);

                const view = calendar.view?.type;
                const mobileViews = ['listWeek', 'dayGridMonth', 'timeGridDay'];

                if (mobile && view && !mobileViews.includes(view)) {
                    calendar.changeView('listWeek');
                }
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: mobileQuery.matches ? 'listWeek' : 'dayGridMonth',
                height: 'auto',
                stickyHeaderDates: !mobileQuery.matches,
                handleWindowResize: true,
                headerToolbar: mobileQuery.matches
                    ? { left: 'prev,next', center: 'title', right: 'today' }
                    : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
                footerToolbar: mobileQuery.matches
                    ? { center: 'listWeek,dayGridMonth,timeGridDay' }
                    : false,
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    listWeek: 'List',
                },
                dayMaxEventRows: mobileQuery.matches ? 2 : true,
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
                windowResize() {
                    applyLayout();
                },
            });

            calendar.render();
        });
    </script>
@endpush
