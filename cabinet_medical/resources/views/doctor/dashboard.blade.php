@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ __('messages.doctor_dashboard') }}</h1>
        <a class="btn btn-primary" href="{{ route('doctor.appointments') }}">{{ __('messages.my_appointments') }}</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="content-card p-3">
                <div class="text-muted small">{{ __('messages.today_appointments') }}</div>
                <div class="display-6 fw-semibold">{{ $appointments->count() }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="content-card p-3">
                <div class="text-muted small">{{ __('messages.next_appointment') }}</div>
                @if ($nextAppointment)
                    <div class="fw-semibold">{{ $nextAppointment->patient->name }}</div>
                    <div class="text-muted">
                        {{ $nextAppointment->appointment_date->format('Y-m-d') }}
                        {{ substr($nextAppointment->appointment_time, 0, 5) }}
                    </div>
                @else
                    <div class="text-muted">{{ __('messages.no_appointments') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="content-card p-3 p-md-4">
        <div class="page-header mb-3">
            <h2 class="h5 mb-0">{{ __('messages.today_appointments') }}</h2>
        </div>

        @include('doctor.partials.appointments-table', ['appointments' => $appointments])
    </div>
@endsection
