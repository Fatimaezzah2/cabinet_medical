@extends('layouts.app')

@section('content')
    <div class="content-card auth-card p-4">
        <h1 class="h3 text-center mb-4">{{ __('messages.login') }}</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

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

            <button class="btn btn-primary w-100 mt-4" type="submit">{{ __('messages.login') }}</button>
        </form>

        <p class="text-center mt-3 mb-0">
            {{ __('messages.need_account') }}
            <a class="link" href="{{ route('register') }}">{{ __('messages.register') }}</a>
        </p>
    </div>
@endsection
