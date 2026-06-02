<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle mb-0">
        <thead class="table-primary">
            <tr>
                <th>{{ __('messages.patient') }}</th>
                <th>{{ __('messages.service') }}</th>
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('messages.time') }}</th>
                <th>{{ __('messages.total_price') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $appointment)
                <tr>
                    <td>
                        <a class="link" href="{{ route('doctor.patients.show', $appointment->patient) }}">
                            {{ $appointment->patient->name }}
                        </a>
                    </td>
                    <td>{{ $appointment->service->name }}</td>
                    <td>{{ $appointment->appointment_date->format('Y-m-d') }}</td>
                    <td>{{ substr($appointment->appointment_time, 0, 5) }}</td>
                    <td>{{ number_format((float) $appointment->total_price, 2) }}</td>
                    <td><span class="badge {{ $appointment->statusBadgeClass() }}">{{ __('messages.' . $appointment->status) }}</span></td>
                    <td>
                        <div class="actions">
                            @if ($appointment->status !== App\Models\Appointment::STATUS_CONFIRMED)
                                <form method="POST" action="{{ route('doctor.appointments.accept', $appointment) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm" type="submit">{{ __('messages.accept') }}</button>
                                </form>
                            @endif

                            @if ($appointment->status !== App\Models\Appointment::STATUS_CANCELLED)
                                <form method="POST" action="{{ route('doctor.appointments.cancel', $appointment) }}">
                                    @csrf
                                    <button class="btn btn-warning btn-sm" type="submit">{{ __('messages.cancel_appointment') }}</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">{{ __('messages.no_appointments') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
