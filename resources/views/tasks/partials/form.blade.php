@php
    $statusOptions = $task->exists
        ? \App\Enums\TaskStatus::cases()
        : \App\Enums\TaskStatus::activeCases();
@endphp

<form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
    @csrf
    @if ($method ?? false)
        @method($method)
    @endif

    <div class="card pm-card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="title">Task Name</label>
                <input id="title" name="title" value="{{ old('title', $task->title) }}" class="form-control @error('title') is-invalid @enderror" required maxlength="255">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="project_id">Project</label>
                    <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                        <option value="">No Project</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) old('project_id', $task->project_id) === (string) $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="developer">Developer</label>
                    <input id="developer" name="developer" value="{{ old('developer', $task->developer) }}" class="form-control @error('developer') is-invalid @enderror" maxlength="120">
                    @error('developer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                        @foreach (\App\Enums\TaskPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $task->priority?->value) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $task->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $task->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-3">
                    <label class="form-label" for="dev_deadline_date">Dev Deadline Date</label>
                    <input id="dev_deadline_date" type="date" name="dev_deadline_date" class="form-control" value="{{ old('dev_deadline_date', $task->dev_deadline?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="dev_deadline_time">Dev Deadline Time</label>
                    <input id="dev_deadline_time" type="time" name="dev_deadline_time" class="form-control" value="{{ old('dev_deadline_time', $task->dev_deadline?->format('H:i')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="client_deadline_date">Client Deadline Date</label>
                    <input id="client_deadline_date" type="date" name="client_deadline_date" class="form-control" value="{{ old('client_deadline_date', $task->client_deadline?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="client_deadline_time">Client Deadline Time</label>
                    <input id="client_deadline_time" type="time" name="client_deadline_time" class="form-control" value="{{ old('client_deadline_time', $task->client_deadline?->format('H:i')) }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-dark" type="submit">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</form>
