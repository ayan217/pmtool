@php
    $statusOptions = $task->exists
        ? \App\Enums\TaskStatus::cases()
        : \App\Enums\TaskStatus::activeCases();
@endphp

<form id="taskForm" method="POST" action="{{ $action }}" class="needs-validation" enctype="multipart/form-data" novalidate>
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
                    <label class="form-label" for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                        @foreach (\App\Enums\TaskPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $task->priority?->value) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $task->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @php
                $developerRows = old('developers');
                if (! is_array($developerRows)) {
                    $developerRows = $task->relationLoaded('developers') || $task->exists
                        ? $task->developers->map(fn ($developer) => [
                            'name' => $developer->name,
                            'email' => $developer->email,
                            'phone' => $developer->phone,
                        ])->all()
                        : [];
                }
                if ($developerRows === []) {
                    $developerRows = [['name' => '', 'email' => '', 'phone' => '']];
                }
            @endphp

            @php
                $developerCatalog = $developerCatalog ?? collect();
            @endphp

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <label class="form-label mb-0">Developers</label>
                        <div class="small text-secondary">Type a name to pick a saved developer. Email and phone fill in automatically. A new name is added to Developers.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="addDeveloperRow">+ Add Developer</button>
                </div>
                @error('developers') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                <datalist id="developerCatalogList">
                    @foreach ($developerCatalog as $catalogDeveloper)
                        <option value="{{ $catalogDeveloper['name'] }}">{{ collect([$catalogDeveloper['email'] ?? null, $catalogDeveloper['phone'] ?? null])->filter()->implode(' · ') }}</option>
                    @endforeach
                </datalist>
                <script type="application/json" id="developerCatalog">@json($developerCatalog->values())</script>
                <div id="developerRows">
                    @foreach ($developerRows as $index => $developer)
                        <div class="developer-row row g-2 align-items-end mb-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Name</label>
                                <input type="text" name="developers[{{ $index }}][name]" value="{{ $developer['name'] ?? '' }}" class="form-control @error('developers.'.$index.'.name') is-invalid @enderror" maxlength="120" placeholder="Rahul" list="developerCatalogList" autocomplete="off" data-developer-name>
                                @error('developers.'.$index.'.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Email</label>
                                <input type="email" name="developers[{{ $index }}][email]" value="{{ $developer['email'] ?? '' }}" class="form-control @error('developers.'.$index.'.email') is-invalid @enderror" maxlength="255" placeholder="rahul@example.com" data-developer-email>
                                @error('developers.'.$index.'.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Phone</label>
                                <input type="text" name="developers[{{ $index }}][phone]" value="{{ $developer['phone'] ?? '' }}" class="form-control @error('developers.'.$index.'.phone') is-invalid @enderror" maxlength="30" placeholder="+91 98765 43210" data-developer-phone>
                                @error('developers.'.$index.'.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100" data-remove-developer aria-label="Remove developer">&times;</button>
                            </div>
                        </div>
                    @endforeach
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

            <div class="mt-4">
                <label class="form-label" for="attachments">Documents</label>
                <div class="small text-secondary mb-2">Attach one or more files. Max {{ config('pm.attachments.max_files') }} files, {{ number_format(config('pm.attachments.max_kilobytes') / 1024) }} MB each.</div>
                @if ($task->exists && $task->attachments->isNotEmpty())
                    <div class="border rounded-3 px-3 mb-3">
                        @include('tasks.partials.attachment-list', ['task' => $task])
                    </div>
                @endif
                <input id="attachments" type="file" name="attachments[]" class="form-control @error('attachments') is-invalid @enderror @error('attachments.0') is-invalid @enderror" multiple>
                @error('attachments') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('attachments.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                <button class="btn btn-dark" type="submit" form="taskForm">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</form>
