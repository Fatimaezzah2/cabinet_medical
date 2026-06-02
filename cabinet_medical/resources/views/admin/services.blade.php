@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.manage_services') }}</h1>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                {{ __('messages.create_service') }}
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.price') }}</th>
                        <th>{{ __('messages.duration') }}</th>
                        <th>{{ __('messages.appointments') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $service->name }}</div>
                                @if ($service->description)
                                    <div class="text-muted small">{{ $service->description }}</div>
                                @endif
                            </td>
                            <td>{{ number_format((float) $service->price, 2) }}</td>
                            <td>{{ $service->duration }} {{ __('messages.minutes') }}</td>
                            <td>{{ $service->appointments_count }}</td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editService{{ $service->id }}">
                                        {{ __('messages.edit') }}
                                    </button>
                                    <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#deleteService{{ $service->id }}">
                                        {{ __('messages.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editService{{ $service->id }}" tabindex="-1" aria-labelledby="editServiceLabel{{ $service->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.services.update', $service) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editServiceLabel{{ $service->id }}">{{ __('messages.edit_service') }}</h5>
                                            <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @include('admin.partials.service-form', ['service' => $service])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                            <button class="btn btn-success" type="submit">{{ __('messages.update') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteService{{ $service->id }}" tabindex="-1" aria-labelledby="deleteServiceLabel{{ $service->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteServiceLabel{{ $service->id }}">{{ __('messages.delete_service') }}</h5>
                                        <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">{{ __('messages.confirm_delete_service', ['name' => $service->name]) }}</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">{{ __('messages.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('messages.no_services') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="createServiceModal" tabindex="-1" aria-labelledby="createServiceLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.services.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createServiceLabel">{{ __('messages.create_service') }}</h5>
                        <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.partials.service-form', ['service' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button class="btn btn-success" type="submit">{{ __('messages.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
