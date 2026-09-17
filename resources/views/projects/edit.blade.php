@extends('layouts.app')

@section('title', 'Edit ' . $project->name . ' — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Edit Project: {{ $project->name }}</h1>
        <p class="cst-page-subtitle">Update project metadata, dates, or operational status.</p>
    </div>
    <div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
            &larr; Back to Project Hub
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('projects.update', $project) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Project Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $project->site_name) }}">
                            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location / City</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $project->location) }}">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $project->status->value) === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(auth()->user()->isAdmin())
                            <div class="col-md-6">
                                <label class="form-label">Project Owner</label>
                                <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_id', $project->owner_id) == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control @error('expected_completion_date') is-invalid @enderror" value="{{ old('expected_completion_date', $project->expected_completion_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $project->notes) }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center border-top pt-3">
                            <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Are you sure you want to delete this project?')) { document.getElementById('deleteProjectForm').submit(); }">
                                <i class="ti ti-trash me-1"></i> Delete Project
                            </button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>

                <form id="deleteProjectForm" action="{{ route('projects.destroy', $project) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
