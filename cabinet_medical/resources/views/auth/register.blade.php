@extends('layouts.app')

@section('content')
    <div class="content-card auth-card p-4">
        <h1 class="h3 text-center mb-4">{{ __('messages.create_account') }}</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label class="form-label" for="name">{{ __('messages.name') }}</label>
            <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror

            <label class="form-label" for="email">{{ __('messages.email') }}</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label class="form-label" for="password">{{ __('messages.password') }}</label>
            <input id="password" class="form-control" type="password" name="password" required>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <label class="form-label" for="password_confirmation">{{ __('messages.confirm_password') }}</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required>

            <button class="btn btn-success w-100 mt-4" type="submit">{{ __('messages.register') }}</button>
        </form>

        <p class="text-center mt-3 mb-0">
            {{ __('messages.already_have_account') }}
            <a class="link" href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </p>
    </div>
@endsection
