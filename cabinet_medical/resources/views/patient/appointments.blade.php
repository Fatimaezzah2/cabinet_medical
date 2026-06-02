@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.my_appointments') }}</h1>
            <a class="btn btn-success" href="{{ route('patient.appointments.create') }}">{{ __('messages.book_appointment') }}</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('patient.partials.appointments-table', ['appointments' => $appointments])
    </div>
@endsection
