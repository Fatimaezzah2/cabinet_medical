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
        <option
            value="{{ $service->id }}"
            data-price="{{ number_format((float) $service->price, 2, '.', '') }}"
            @selected(old('service_id', $appointment->service_id ?? '') == $service->id)
        >
            {{ $service->name }} - {{ number_format((float) $service->price, 2) }}
        </option>
    @endforeach
</select>
@error('service_id')
    <div class="error">{{ $message }}</div>
@enderror

<div class="alert alert-info mt-3 mb-0">
    {{ __('messages.service_price') }}:
    <strong id="selectedServicePrice">{{ isset($appointment) ? number_format((float) $appointment->service->price, 2) : __('messages.choose_service') }}</strong>
</div>

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

@push('scripts')
    <script>
        const patientServiceSelect = document.getElementById('service_id');
        const selectedServicePrice = document.getElementById('selectedServicePrice');
        const chooseServiceLabel = @json(__('messages.choose_service'));

        function updateSelectedServicePrice() {
            const selectedOption = patientServiceSelect.options[patientServiceSelect.selectedIndex];
            selectedServicePrice.textContent = selectedOption?.dataset.price || chooseServiceLabel;
        }

        patientServiceSelect.addEventListener('change', updateSelectedServicePrice);
        updateSelectedServicePrice();
    </script>
@endpush
