@extends('layouts.app')

@section('content')
    <div class="content-card p-3 p-md-4">
        <div class="page-header">
            <h1 class="h3 mb-0">{{ __('messages.manage_users') }}</h1>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">
                {{ __('messages.create_user') }}
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
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.role') }}</th>
                        <th>{{ __('messages.approved') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ __('messages.' . $user->role) }}</td>
                            <td>
                                <span class="badge {{ $user->is_approved ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $user->is_approved ? __('messages.yes') : __('messages.no') }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}">
                                        {{ __('messages.edit') }}
                                    </button>

                                    @unless ($user->is(auth()->user()))
                                        <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#deleteUser{{ $user->id }}">
                                            {{ __('messages.delete') }}
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $user->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editUserLabel{{ $user->id }}">{{ __('messages.edit_user') }}</h5>
                                            <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @include('admin.partials.user-form', ['user' => $user, 'roles' => $roles, 'passwordRequired' => false])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                            <button class="btn btn-success" type="submit">{{ __('messages.update') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteUser{{ $user->id }}" tabindex="-1" aria-labelledby="deleteUserLabel{{ $user->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteUserLabel{{ $user->id }}">{{ __('messages.delete_user') }}</h5>
                                        <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">{{ __('messages.confirm_delete_user', ['name' => $user->name]) }}</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">{{ __('messages.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserLabel">{{ __('messages.create_user') }}</h5>
                        <button type="button" class="btn-close mt-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.partials.user-form', ['user' => null, 'roles' => $roles, 'passwordRequired' => true])
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
