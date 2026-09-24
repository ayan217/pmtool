@php
    $quick = $filters['quick'] ?? null;
    $quickFilters = [
        'all' => 'All',
        'today' => 'Today',
        'tomorrow' => 'Tomorrow',
        'this_week' => 'This Week',
        'overdue' => 'Overdue',
        'in_progress' => 'In Progress',
        'on_client_review' => 'On Client Review',
        'completed' => 'Completed',
        'archived' => 'Archived',
        'no_project' => 'No Project',
    ];
@endphp

<form method="GET" action="{{ $action }}" class="card pm-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h6 mb-0">Filters</h2>
            <div class="d-flex gap-2">
                <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
                <button class="btn btn-sm btn-dark" type="submit">Apply filters</button>
            </div>
        </div>

        <div class="quick-filters d-flex flex-wrap gap-2 mb-3">
            @foreach ($quickFilters as $value => $label)
                <a href="{{ request()->fullUrlWithQuery(['quick' => $value, 'page' => null]) }}"
                   class="btn btn-sm {{ $quick === $value ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if (filled($quick) && $quick !== 'all')
            <input type="hidden" name="quick" value="{{ $quick }}">
        @endif

        <div class="mb-3">
            <label class="form-label">Search</label>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Title, notes, comments, project...">
        </div>

        <button class="btn btn-sm btn-outline-secondary d-lg-none mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
            More filters
        </button>

        <div class="collapse d-lg-block" id="advancedFilters">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Project</label>
                <select name="project" class="form-select">
                    <option value="">Any</option>
                    <option value="none" @selected(($filters['project'] ?? '') === 'none')>No Project</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) ($filters['project'] ?? '') === (string) $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Developer</label>
                <input list="developerOptions" name="developer" value="{{ $filters['developer'] ?? '' }}" class="form-control">
                <datalist id="developerOptions">
                    @foreach ($developers as $developer)
                        <option value="{{ $developer }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Any</option>
                    @foreach (\App\Enums\TaskStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">Any</option>
                    @foreach (\App\Enums\TaskPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dev Deadline</label>
                <select name="dev_deadline" class="form-select">
                    <option value="">Any</option>
                    @foreach (['overdue' => 'Overdue', 'today' => 'Today', 'tomorrow' => 'Tomorrow', 'this_week' => 'This Week', 'upcoming' => 'Upcoming'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['dev_deadline'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Client Deadline</label>
                <select name="client_deadline" class="form-select">
                    <option value="">Any</option>
                    @foreach (['overdue' => 'Overdue', 'today' => 'Today', 'tomorrow' => 'Tomorrow', 'this_week' => 'This Week', 'upcoming' => 'Upcoming'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['client_deadline'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Completed from</label>
                <input type="date" name="completed_from" value="{{ $filters['completed_from'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Completed to</label>
                <input type="date" name="completed_to" value="{{ $filters['completed_to'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Per page</label>
                <select name="per_page" class="form-select">
                    @foreach (config('pm.per_page_options') as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        </div>
    </div>
</form>
