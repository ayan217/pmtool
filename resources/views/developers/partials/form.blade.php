<form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
    @csrf
    @if ($method ?? false)
        @method($method)
    @endif

    <div class="card pm-card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $developer->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="120">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $developer->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="255">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="form-label" for="phone">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $developer->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="30">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-dark" type="submit">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</form>
