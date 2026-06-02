<label class="form-label" for="patient_id">{{ __('messages.patient') }}</label>
<select id="patient_id" class="form-select" name="patient_id" required>
    <option value="">{{ __('messages.choose_patient') }}</option>
    @foreach ($patients as $patient)
        <option value="{{ $patient->id }}" @selected(old('patient_id', $appointment->patient_id ?? '') == $patient->id)>
            {{ $patient->name }}
        </option>
    @endforeach
</select>
@error('patient_id')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="doctor_id">{{ __('messages.doctor') }}</label>
<select id="doctor_id" class="form-select" name="doctor_id" required>
    <option value="">{{ __('messages.choose_doctor') }}</option>
    @foreach ($doctors as $doctor)
        <option value="{{ $doctor->id }}" @selected(old('doctor_id', $appointment->doctor_id ?? '') == $doctor->id)>
            {{ $doctor->name }}
        </option>
    @endforeach
</select>
@error('doctor_id')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="service_id">{{ __('messages.service') }}</label>
<select id="service_id" class="form-select" name="service_id" required>
    <option value="">{{ __('messages.choose_service') }}</option>
    @foreach ($services as $service)
        <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id ?? '') == $service->id)>
            {{ $service->name }}
        </option>
    @endforeach
</select>
@error('service_id')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="appointment_date">{{ __('messages.date') }}</label>
<input id="appointment_date" class="form-control" type="date" name="appointment_date" value="{{ old('appointment_date', isset($appointment) ? $appointment->appointment_date->format('Y-m-d') : '') }}" required>
@error('appointment_date')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="appointment_time">{{ __('messages.time') }}</label>
<input id="appointment_time" class="form-control" type="time" name="appointment_time" value="{{ old('appointment_time', isset($appointment) ? substr($appointment->appointment_time, 0, 5) : '') }}" required>
@error('appointment_time')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="status">{{ __('messages.status') }}</label>
<select id="status" class="form-select" name="status" required>
    @foreach ($statuses as $status)
        <option value="{{ $status }}" @selected(old('status', $appointment->status ?? 'pending') === $status)>
            {{ __('messages.' . $status) }}
        </option>
    @endforeach
</select>
@error('status')
    <div class="error">{{ $message }}</div>
@enderror
