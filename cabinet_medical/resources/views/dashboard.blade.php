@extends('layouts.app')

@section('content')
    <div class="content-card p-4">
        <h1 class="h3 mb-3">{{ __('messages.dashboard') }}</h1>

        <p>{{ __('messages.welcome', ['name' => auth()->user()->name]) }}</p>
        <p>{{ __('messages.your_role') }} <strong>{{ __('messages.' . auth()->user()->role) }}</strong></p>

        @if (auth()->user()->isAdmin())
            <a class="btn btn-success" href="{{ route('admin.dashboard') }}">{{ __('messages.admin_dashboard') }}</a>
        @endif

        @if (auth()->user()->isDoctor())
            <a class="btn btn-success" href="{{ route('doctor.dashboard') }}">{{ __('messages.doctor_dashboard') }}</a>
        @endif

        @if (auth()->user()->isPatient())
            <a class="btn btn-success" href="{{ route('patient.dashboard') }}">{{ __('messages.patient_dashboard') }}</a>
        @endif

        <a class="btn btn-primary" href="{{ route('appointments.index') }}">{{ __('messages.manage_appointments') }}</a>
    </div>
@endsection
