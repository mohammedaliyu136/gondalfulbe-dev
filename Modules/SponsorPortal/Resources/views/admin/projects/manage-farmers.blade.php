@extends('layouts.admin')
@section('page-title', __('Manage Farmers') . ' — ' . $project->title)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsors.show', $sponsor->id) }}">{{ $sponsor->organization_name }}</a></li>
    <li class="breadcrumb-item active">{{ __('Farmers') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Farmers for') }} <em>{{ $project->title }}</em></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sponsors.sync-farmers', [$sponsor->id, $project->id]) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Beneficiary Farmers') }}</label>
                        <select name="farmer_ids[]" class="form-select" multiple size="12">
                            @foreach($farmers as $f)
                            <option value="{{ $f->id }}" @selected(in_array($f->id, $assignedIds))>
                                {{ $f->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple. Currently assigned are highlighted.') }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <a href="{{ route('admin.sponsors.show', $sponsor->id) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
