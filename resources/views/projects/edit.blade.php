@extends('layouts.app')

@section('title', 'Edit ' . $project->name . ' — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-edit me-2 text-primary"></i> Edit Project: {{ $project->name }}
            </h4>
            <div class="pos-glass-intro-sub">Update project metadata, target dates, or operational workflow status.</div>
        </div>
        <div>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Project Hub
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('projects.update', $project) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Project Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $project->site_name) }}">
                            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / City</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $project->location) }}">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
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
                                <label class="form-label fw-semibold">Project Owner</label>
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
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control @error('expected_completion_date') is-invalid @enderror" value="{{ old('expected_completion_date', $project->expected_completion_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $project->notes) }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center border-top pt-3">
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(() => document.getElementById('deleteProjectForm').submit(), 'Are you sure you want to delete this project? All associated logs, expenses and allocations will be deleted.')">
                                <i class="icon-base ti tabler-trash me-1"></i> Delete Project
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
                            </button>
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
