@extends('layouts.app')

@section('content')
    <div class="content-card p-4">
        <h1 class="h3 mb-3">{{ __('messages.create_appointment') }}</h1>

        <form method="POST" action="{{ route('appointments.store') }}">
            @csrf

            @include('appointments.form')

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-success" type="submit">{{ __('messages.save') }}</button>
                <a class="btn btn-secondary" href="{{ route('appointments.index') }}">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
