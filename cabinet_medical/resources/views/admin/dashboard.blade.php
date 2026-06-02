@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ __('messages.admin_dashboard') }}</h1>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            'total_users' => __('messages.total_users'),
            'total_doctors' => __('messages.total_doctors'),
            'total_patients' => __('messages.total_patients'),
            'total_services' => __('messages.total_services'),
            'total_appointments' => __('messages.total_appointments'),
            'today_appointments' => __('messages.today_appointments'),
        ] as $key => $label)
            <div class="col-sm-6 col-xl-4">
                <div class="content-card p-3">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="display-6 fw-semibold">{{ $stats[$key] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="content-card p-3 p-md-4">
        <div class="page-header mb-3">
            <h2 class="h5 mb-0">{{ __('messages.today_appointments') }}</h2>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.appointments') }}">{{ __('messages.view_all') }}</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>{{ __('messages.patient') }}</th>
                        <th>{{ __('messages.doctor') }}</th>
                        <th>{{ __('messages.service') }}</th>
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
                            <td>{{ substr($appointment->appointment_time, 0, 5) }}</td>
                            <td>{{ number_format((float) $appointment->total_price, 2) }}</td>
                            <td><span class="badge {{ $appointment->statusBadgeClass() }}">{{ __('messages.' . $appointment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ __('messages.no_appointments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card p-3 p-md-4 mt-4">
        <div class="page-header mb-3">
            <h2 class="h5 mb-0">{{ __('messages.activity_logs') }}</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>{{ __('messages.user') }}</th>
                        <th>{{ __('messages.action') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th>{{ __('messages.created_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityLogs as $activityLog)
                        <tr>
                            <td>{{ $activityLog->user?->name ?? '-' }}</td>
                            <td>{{ __('messages.' . $activityLog->action) }}</td>
                            <td>{{ $activityLog->description }}</td>
                            <td>{{ $activityLog->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">{{ __('messages.no_activity_logs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
