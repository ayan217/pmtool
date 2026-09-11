<form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
    @csrf
    @if ($method ?? false)
        @method($method)
    @endif

    <div class="card pm-card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $project->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    @foreach (\App\Enums\ProjectStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $project->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-dark" type="submit">Save Project</button>
            </div>
        </div>
    </div>
</form>
