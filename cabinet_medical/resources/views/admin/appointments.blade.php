@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.all_appointments') }}</h1>
            <a class="btn btn-success" href="{{ route('appointments.create') }}">{{ __('messages.new_appointment') }}</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>{{ __('messages.patient') }}</th>
                        <th>{{ __('messages.doctor') }}</th>
                        <th>{{ __('messages.service') }}</th>
                        <th>{{ __('messages.service_price') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.time') }}</th>
                        <th>{{ __('messages.total_price') }}</th>
                        <th>{{ __('messages.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->patient->name }}</td>
                            <td>{{ $appointment->doctor->name }}</td>
                            <td>{{ $appointment->service->name }}</td>
                            <td>{{ number_format((float) $appointment->service->price, 2) }}</td>
                            <td>{{ $appointment->appointment_date->format('Y-m-d') }}</td>
                            <td>{{ substr($appointment->appointment_time, 0, 5) }}</td>
                            <td>{{ number_format((float) $appointment->total_price, 2) }}</td>
                            <td><span class="badge {{ $appointment->statusBadgeClass() }}">{{ __('messages.' . $appointment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">{{ __('messages.no_appointments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
