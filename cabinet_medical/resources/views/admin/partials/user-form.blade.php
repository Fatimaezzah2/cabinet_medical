<label class="form-label" for="name{{ $user?->id ?? 'Create' }}">{{ __('messages.name') }}</label>
<input id="name{{ $user?->id ?? 'Create' }}" class="form-control" type="text" name="name" value="{{ old('name', $user?->name) }}" required>
@error('name')
    <div class="error">{{ $message }}</div>
@enderror

<label class="form-label" for="email{{ $user?->id ?? 'Create' }}">{{ __('messages.email') }}</label>
<input id="email{{ $user?->id ?? 'Create' }}" class="form-control" type="email" name="email" value="{{ old('email', $user?->email) }}" required>
@error('email')
    <div class="error">{{ $message }}</div>
@enderror

<div class="row">
    <div class="col-md-6">
        <label class="form-label" for="password{{ $user?->id ?? 'Create' }}">{{ __('messages.password') }}</label>
        <input id="password{{ $user?->id ?? 'Create' }}" class="form-control" type="password" name="password" @required($passwordRequired)>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password_confirmation{{ $user?->id ?? 'Create' }}">{{ __('messages.confirm_password') }}</label>
        <input id="password_confirmation{{ $user?->id ?? 'Create' }}" class="form-control" type="password" name="password_confirmation" @required($passwordRequired)>
    </div>
</div>

<label class="form-label" for="role{{ $user?->id ?? 'Create' }}">{{ __('messages.role') }}</label>
<select id="role{{ $user?->id ?? 'Create' }}" class="form-select" name="role" required>
    @foreach ($roles as $role)
        <option value="{{ $role }}" @selected(old('role', $user?->role ?? App\Models\User::ROLE_PATIENT) === $role)>{{ __('messages.' . $role) }}</option>
    @endforeach
</select>
@error('role')
    <div class="error">{{ $message }}</div>
@enderror

<div class="form-check mt-3">
    <input id="is_approved{{ $user?->id ?? 'Create' }}" class="form-check-input" type="checkbox" name="is_approved" value="1" @checked(old('is_approved', $user?->is_approved ?? true))>
    <label class="form-check-label mt-0" for="is_approved{{ $user?->id ?? 'Create' }}">{{ __('messages.approved') }}</label>
</div>
