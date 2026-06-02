@extends('layouts.app')

@section('content')
    <div class="content-card p-4">
        <h1 class="h3 mb-3">{{ __('messages.edit_appointment') }}</h1>

        <form method="POST" action="{{ route('patient.appointments.update', $appointment) }}">
            @csrf
            @method('PUT')

            @include('patient.partials.appointment-form')

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-success" type="submit">{{ __('messages.update') }}</button>
                <a class="btn btn-secondary" href="{{ route('patient.appointments') }}">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
