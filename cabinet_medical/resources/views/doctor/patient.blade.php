@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4 mb-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.patient_details') }}</h1>
            <a class="btn btn-secondary" href="{{ route('doctor.appointments') }}">{{ __('messages.my_appointments') }}</a>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">{{ __('messages.name') }}</div>
                <div class="fw-semibold">{{ $patient->name }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">{{ __('messages.email') }}</div>
                <div class="fw-semibold">{{ $patient->email }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">{{ __('messages.role') }}</div>
                <div class="fw-semibold">{{ __('messages.' . $patient->role) }}</div>
            </div>
        </div>
    </div>

    <div class="content-card p-3 p-md-4">
        <div class="page-header mb-3">
            <h2 class="h5 mb-0">{{ __('messages.appointments') }}</h2>
        </div>

        @include('doctor.partials.appointments-table', ['appointments' => $appointments])
    </div>
@endsection
