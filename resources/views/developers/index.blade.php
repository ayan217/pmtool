@extends('layouts.app')

@section('title', 'Developers')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">People directory</p>
            <h1 class="page-title h3 mb-0">Developers</h1>
        </div>
        <a href="{{ route('developers.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Developer</a>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            @if ($developers->isEmpty())
                <x-empty-state title="No developers yet." action-url="{{ route('developers.create') }}" action-label="+ New Developer">
                    Save names, emails, and phone numbers here. They will autocomplete when you assign someone to a task.
                </x-empty-state>
            @else
                <div class="d-none d-md-block table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Tasks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($developers as $developer)
                                <tr>
                                    <td class="fw-semibold">{{ $developer->name }}</td>
                                    <td>{{ $developer->email ?: '—' }}</td>
                                    <td>{{ $developer->phone ?: '—' }}</td>
                                    <td>{{ $assignmentCounts[mb_strtolower($developer->name)] ?? 0 }}</td>
                                    <td class="text-nowrap text-end">
                                        <a href="{{ route('developers.edit', $developer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="POST" action="{{ route('developers.destroy', $developer) }}" class="d-inline" data-confirm-form="Remove {{ $developer->name }} from the directory? Tasks they are assigned to will stay as they are.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none">
                    @foreach ($developers as $developer)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-semibold">{{ $developer->name }}</div>
                            <div class="small text-secondary mt-1">
                                {{ $developer->email ?: 'No email' }}
                                ·
                                {{ $developer->phone ?: 'No phone' }}
                            </div>
                            <div class="small text-secondary mt-1">{{ $assignmentCounts[mb_strtolower($developer->name)] ?? 0 }} assigned {{ \Illuminate\Support\Str::plural('task', $assignmentCounts[mb_strtolower($developer->name)] ?? 0) }}</div>
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('developers.edit', $developer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('developers.destroy', $developer) }}" data-confirm-form="Remove {{ $developer->name }} from the directory? Tasks they are assigned to will stay as they are.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
