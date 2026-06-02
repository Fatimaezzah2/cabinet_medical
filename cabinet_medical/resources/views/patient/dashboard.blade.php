@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ __('messages.patient_dashboard') }}</h1>
        <a class="btn btn-success" href="{{ route('patient.appointments.create') }}">{{ __('messages.book_appointment') }}</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="content-card p-3 p-md-4 mb-4">
        <div class="text-muted small">{{ __('messages.next_appointment') }}</div>
        @if ($nextAppointment)
            <div class="h5 mt-2 mb-1">{{ $nextAppointment->doctor->name }}</div>
            <div>{{ $nextAppointment->service->name }}</div>
            <div class="text-muted">
                {{ $nextAppointment->appointment_date->format('Y-m-d') }}
                {{ substr($nextAppointment->appointment_time, 0, 5) }}
            </div>
            <div class="fw-semibold mt-2">{{ __('messages.total_price') }}: {{ number_format((float) $nextAppointment->total_price, 2) }}</div>
        @else
            <div class="text-muted mt-2">{{ __('messages.no_appointments') }}</div>
        @endif
    </div>

    <div class="content-card p-3 p-md-4">
        <div class="page-header mb-3">
            <h2 class="h5 mb-0">{{ __('messages.my_appointments') }}</h2>
            <a class="btn btn-primary btn-sm" href="{{ route('patient.appointments') }}">{{ __('messages.view_all') }}</a>
        </div>

        @include('patient.partials.appointments-table', ['appointments' => $appointments])
    </div>
@endsection
