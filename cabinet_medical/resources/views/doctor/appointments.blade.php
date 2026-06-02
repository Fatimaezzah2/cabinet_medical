@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.my_appointments') }}</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('doctor.partials.appointments-table', ['appointments' => $appointments])
    </div>
@endsection
