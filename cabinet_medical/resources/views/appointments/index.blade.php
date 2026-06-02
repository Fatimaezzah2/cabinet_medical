@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.appointments') }}</h1>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">
                {{ __('messages.add_appointment') }}
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form id="appointmentSearchForm" class="mb-3">
            <input id="appointmentSearch" class="form-control" type="text" name="q"
                placeholder="{{ __('messages.search_patient') }}">

            <div id="searchLoading" class="d-none mt-2">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span class="ms-2">{{ __('messages.search') }}...</span>
            </div>
        </form>

        <p id="noAppointmentsMessage" class="alert alert-info"
            @if ($appointments->isNotEmpty()) style="display: none;" @endif>
            {{ __('messages.no_appointments') }}
        </p>

        <div class="table-responsive">
            <table id="appointmentsTable" class="table table-bordered table-striped table-hover align-middle mb-0"
                @if ($appointments->isEmpty()) style="display: none;" @endif>
                <thead class="table-primary">
                    <tr>
                        <th>{{ __('messages.patient') }}</th>
                        <th>{{ __('messages.doctor') }}</th>
                        <th>{{ __('messages.service') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.time') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="appointmentsTableBody">
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->patient->name }}</td>
                            <td>{{ $appointment->doctor->name }}</td>
                            <td>{{ $appointment->service->name }}</td>
                            <td>{{ $appointment->appointment_date->format('Y-m-d') }}</td>
                            <td>{{ substr($appointment->appointment_time, 0, 5) }}</td>
                            <td><span
                                    class="badge {{ $appointment->statusBadgeClass() }}">{{ __('messages.' . $appointment->status) }}</span>
                            </td>
                            <td>
                                <div class="actions">
                                    @if (auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('appointments.confirm', $appointment) }}"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm"
                                                type="submit">{{ __('messages.confirm') }}</button>
                                        </form>

                                        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-warning btn-sm"
                                                type="submit">{{ __('messages.cancel_appointment') }}</button>
                                        </form>

                                        <button class="btn btn-danger btn-sm delete-appointment-button" type="button"
                                            data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal"
                                            data-id="{{ $appointment->id }}"
                                            data-patient="{{ $appointment->patient->name }}">
                                            {{ __('messages.delete') }}
                                        </button>
                                    @elseif (auth()->user()->isDoctor() && $appointment->doctor_id === auth()->id())
                                        <form method="POST"
                                            action="{{ route('doctor.appointments.accept', $appointment) }}"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm"
                                                type="submit">{{ __('messages.confirm') }}</button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('doctor.appointments.cancel', $appointment) }}"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-warning btn-sm"
                                                type="submit">{{ __('messages.cancel_appointment') }}</button>
                                        </form>
                                    @elseif (auth()->user()->isPatient() && $appointment->patient_id === auth()->id())
                                        <form method="POST"
                                            action="{{ route('patient.appointments.cancel', $appointment) }}"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-warning btn-sm"
                                                type="submit">{{ __('messages.cancel_appointment') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-labelledby="deleteAppointmentLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAppointmentLabel">{{ __('messages.delete_appointment') }}</h5>
                    <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="deleteAppointmentText" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <form id="deleteAppointmentForm" method="POST" action="#">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">{{ __('messages.delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-labelledby="createAppointmentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createAppointmentForm" method="POST" action="{{ route('appointments.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="createAppointmentLabel">{{ __('messages.create_appointment') }}</h5>
                        <button type="button" class="btn-close mt-0" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div id="createAppointmentErrors" class="alert alert-danger d-none"></div>

                        <label class="form-label" for="modal_patient_id">{{ __('messages.patient') }}</label>
                        <select id="modal_patient_id" class="form-select" name="patient_id" required>
                            <option value="">{{ __('messages.choose_patient') }}</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label" for="modal_doctor_id">{{ __('messages.doctor') }}</label>
                        <select id="modal_doctor_id" class="form-select" name="doctor_id" required>
                            <option value="">{{ __('messages.choose_doctor') }}</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label" for="modal_service_id">{{ __('messages.service') }}</label>
                        <select id="modal_service_id" class="form-select" name="service_id" required>
                            <option value="">{{ __('messages.choose_service') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    data-price="{{ number_format((float) $service->price, 2, '.', '') }}">
                                    {{ $service->name }} - {{ number_format((float) $service->price, 2) }} MAD
                                </option>
                            @endforeach
                        </select>

                        <div class="alert alert-info mt-3 mb-0">
                            {{ __('messages.service_price') }}:
                            <strong id="modalServicePrice">{{ __('messages.choose_service') }}</strong>
                        </div>

                        <label class="form-label" for="modal_appointment_date">{{ __('messages.date') }}</label>
                        <input id="modal_appointment_date" class="form-control" type="date" name="appointment_date"
                            required>

                        <label class="form-label" for="modal_appointment_time">{{ __('messages.time') }}</label>
                        <input id="modal_appointment_time" class="form-control" type="time" name="appointment_time"
                            required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('messages.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@if ($errors->any())
    @push('scripts')
        <script>
            const createAppointmentModal = new bootstrap.Modal(document.getElementById('createAppointmentModal'));
            createAppointmentModal.show();
        </script>
    @endpush
@endif

@push('scripts')
    @php
        $searchLabels = [
            'cancelAppointment' => __('messages.cancel_appointment'),
            'confirm' => __('messages.confirm'),
            'edit' => __('messages.edit'),
            'delete' => __('messages.delete'),
            'confirmDelete' => __('messages.confirm_delete_appointment', ['name' => ':name']),
            'statuses' => [
                'pending' => __('messages.pending'),
                'confirmed' => __('messages.confirmed'),
                'cancelled' => __('messages.cancelled'),
            ],
            'statusClasses' => [
                'pending' => 'bg-warning text-dark',
                'confirmed' => 'bg-success',
                'cancelled' => 'bg-danger',
            ],
        ];
    @endphp

    <script>
        const searchForm = document.getElementById('appointmentSearchForm');
        const searchInput = document.getElementById('appointmentSearch');
        const searchLoading = document.getElementById('searchLoading');
        const table = document.getElementById('appointmentsTable');
        const tableBody = document.getElementById('appointmentsTableBody');
        const noAppointmentsMessage = document.getElementById('noAppointmentsMessage');
        const deleteForm = document.getElementById('deleteAppointmentForm');
        const deleteText = document.getElementById('deleteAppointmentText');
        const createAppointmentForm = document.getElementById('createAppointmentForm');
        const createAppointmentErrors = document.getElementById('createAppointmentErrors');
        const createAppointmentElement = document.getElementById('createAppointmentModal');
        const createAppointmentModalInstance = bootstrap.Modal.getOrCreateInstance(createAppointmentElement);
        const modalServiceSelect = document.getElementById('modal_service_id');
        const modalServicePrice = document.getElementById('modalServicePrice');

        const searchUrl = @json(route('appointments.search'));
        const storeUrl = isPatient ?
            @json(route('patient.appointments.store')) :
            @json(route('appointments.store'));
        const deleteUrl = @json(route('appointments.destroy', ':id'));
        const confirmUrl = @json(route('appointments.confirm', ':id'));
        const cancelUrl = @json(route('appointments.cancel', ':id'));
        const csrfToken = @json(csrf_token());
        const isAdmin = @json(auth()->user()->isAdmin());
        const isDoctor = @json(auth()->user()->isDoctor());
        const isPatient = @json(auth()->user()->isPatient());
        const currentUserId = @json(auth()->id());
        const doctorConfirmUrl = @json(route('doctor.appointments.accept', ':id'));
        const doctorCancelUrl = @json(route('doctor.appointments.cancel', ':id'));
        const patientCancelUrl = @json(route('patient.appointments.cancel', ':id'));
        const labels = @json($searchLabels);
        const chooseServiceLabel = @json(__('messages.choose_service'));

        function formatPrice(value) {
            return `${Number(value || 0).toFixed(2)} MAD`;
        }

        function normalizeAppointment(appointment) {
            if (
                typeof appointment.patient === 'object' &&
                typeof appointment.doctor === 'object' &&
                typeof appointment.service === 'object'
            ) {
                return {
                    id: appointment.id,
                    patient_name: appointment.patient.name,
                    patient_id: appointment.patient.id,
                    doctor_name: appointment.doctor.name,
                    doctor_id: appointment.doctor.id,
                    service_name: appointment.service.name,
                    date: appointment.appointment_date,
                    time: appointment.appointment_time,
                    status: appointment.status,
                    edit_url: @json(route('appointments.edit', ':id')).replace(':id', appointment.id),
                };
            }

            return {
                id: appointment.id,
                patient_name: appointment.patient,
                patient_id: appointment.patient_id,
                doctor_name: appointment.doctor,
                doctor_id: appointment.doctor_id,
                service_name: appointment.service,
                date: appointment.date,
                time: appointment.time,
                status: appointment.status,
                edit_url: appointment.edit_url,
            };
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                } [character];
            });
        }

        function renderAppointments(appointments) {
            tableBody.innerHTML = '';

            if (appointments.length === 0) {
                table.style.display = 'none';
                noAppointmentsMessage.style.display = 'block';
                return;
            }

            table.style.display = '';
            noAppointmentsMessage.style.display = 'none';

            appointments.map(normalizeAppointment).forEach(function(appointment) {
                let actionsHtml = '';

                // Admin: confirm, cancel, delete
                if (isAdmin) {
                    actionsHtml += `
                        <form method="POST" action="${confirmUrl.replace(':id', appointment.id)}" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button class="btn btn-success btn-sm" type="submit">${labels.confirm}</button>
                        </form>
                        <form method="POST" action="${cancelUrl.replace(':id', appointment.id)}" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button class="btn btn-warning btn-sm" type="submit">${labels.cancelAppointment}</button>
                        </form>
                        <button class="btn btn-danger btn-sm delete-appointment-button" type="button" data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal" data-id="${appointment.id}" data-patient="${escapeHtml(appointment.patient_name)}">${labels.delete}</button>
                    `;
                }

                // Doctor: confirm/cancel for own appointments
                if (isDoctor && appointment.doctor_id === currentUserId) {
                    actionsHtml += `
                        <form method="POST" action="${doctorConfirmUrl.replace(':id', appointment.id)}" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button class="btn btn-success btn-sm" type="submit">${labels.confirm}</button>
                        </form>
                        <form method="POST" action="${doctorCancelUrl.replace(':id', appointment.id)}" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button class="btn btn-warning btn-sm" type="submit">${labels.cancelAppointment}</button>
                        </form>
                    `;
                }

                // Patient: cancel own
                if (isPatient && appointment.patient_id === currentUserId) {
                    actionsHtml += `
                        <form method="POST" action="${patientCancelUrl.replace(':id', appointment.id)}" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button class="btn btn-warning btn-sm" type="submit">${labels.cancelAppointment}</button>
                        </form>
                    `;
                }

                tableBody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(appointment.patient_name)}</td>
                        <td>${escapeHtml(appointment.doctor_name)}</td>
                        <td>${escapeHtml(appointment.service_name)}</td>
                        <td>${escapeHtml(appointment.date)}</td>
                        <td>${escapeHtml(appointment.time)}</td>
                        <td><span class="badge ${labels.statusClasses[appointment.status] ?? 'bg-secondary'}">${escapeHtml(labels.statuses[appointment.status] ?? appointment.status)}</span></td>
                        <td>
                            <div class="actions">
                                ${actionsHtml}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        function debounce(callback, delay = 300) {
            let timeout;

            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    callback.apply(this, args);
                }, delay);
            };
        }

        function setSearching(isSearching) {
            if (searchLoading) {
                if (isSearching) {
                    searchLoading.classList.remove('d-none');
                } else {
                    searchLoading.classList.add('d-none');
                }
            }

        }

        function refreshAppointments() {
            const q = (searchInput && searchInput.value) ? searchInput.value.trim() : '';

            setSearching(true);

            axios.get(searchUrl, {
                params: {
                    q
                },
            }).then(function(response) {
                renderAppointments(response.data);
            }).catch(function(error) {
                console.error('Appointment search failed.', error);
            }).finally(function() {
                setSearching(false);
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function(event) {
                event.preventDefault();
                refreshAppointments();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', debounce(refreshAppointments, 300));

            if (searchInput.value && searchInput.value.trim()) {
                refreshAppointments();
            }
        }

        modalServiceSelect.addEventListener('change', function() {
            const selectedOption = modalServiceSelect.options[modalServiceSelect.selectedIndex];
            modalServicePrice.textContent = selectedOption?.dataset.price ? formatPrice(selectedOption.dataset
                .price) : chooseServiceLabel;
        });

        createAppointmentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            createAppointmentErrors.classList.add('d-none');
            createAppointmentErrors.innerHTML = '';

            axios.post(storeUrl, Object.fromEntries(new FormData(createAppointmentForm)))
                .then(function() {
                    createAppointmentForm.reset();
                    modalServicePrice.textContent = chooseServiceLabel;
                    createAppointmentModalInstance.hide();
                    refreshAppointments();
                })
                .catch(function(error) {
                    if (error.response?.status === 422) {
                        const errors = Object.values(error.response.data.errors || {}).flat();
                        createAppointmentErrors.innerHTML = errors.map(escapeHtml).join('<br>');
                        createAppointmentErrors.classList.remove('d-none');
                        return;
                    }

                    createAppointmentErrors.textContent = error.response?.data?.message ||
                        'Unable to create appointment.';
                    createAppointmentErrors.classList.remove('d-none');
                });
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-appointment-button');

            if (!button) {
                return;
            }

            const patient = button.dataset.patient;

            deleteForm.action = deleteUrl.replace(':id', button.dataset.id);
            deleteText.textContent = labels.confirmDelete.replace(':name', patient);
        });
    </script>
@endpush
