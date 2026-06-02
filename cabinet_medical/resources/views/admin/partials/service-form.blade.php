<label class="form-label" for="service_name{{ $service?->id ?? 'Create' }}">{{ __('messages.name') }}</label>
<input id="service_name{{ $service?->id ?? 'Create' }}" class="form-control" type="text" name="name" value="{{ old('name', $service?->name) }}" required>
@error('name')
    <div class="error">{{ $message }}</div>
@enderror

<div class="row">
    <div class="col-md-6">
        <label class="form-label" for="price{{ $service?->id ?? 'Create' }}">{{ __('messages.price') }}</label>
        <input id="price{{ $service?->id ?? 'Create' }}" class="form-control" type="number" name="price" min="0" step="0.01" value="{{ old('price', $service?->price) }}" required>
        @error('price')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="duration{{ $service?->id ?? 'Create' }}">{{ __('messages.duration') }}</label>
        <input id="duration{{ $service?->id ?? 'Create' }}" class="form-control" type="number" name="duration" min="1" step="1" value="{{ old('duration', $service?->duration) }}" required>
        @error('duration')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<label class="form-label" for="description{{ $service?->id ?? 'Create' }}">{{ __('messages.description') }}</label>
<textarea id="description{{ $service?->id ?? 'Create' }}" class="form-control" name="description" rows="3">{{ old('description', $service?->description) }}</textarea>
@error('description')
    <div class="error">{{ $message }}</div>
@enderror
