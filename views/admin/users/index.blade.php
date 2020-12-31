@extends('admin.layouts.base')
@php
    /**@var \Illuminate\Auth\Authenticatable|\App\Models\User $current_user*/
    $isAuthUserSuperAdmin = $current_user->isInRole([\App\Models\Role::ROLE_SUPER_ADMIN]);
    $isAuthUserAdmin = $current_user->isInRole([\App\Models\Role::ROLE_ADMIN]);
@endphp

@section('page-title')
    <title>{{__('a.Users')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Users')}}</h1>
            </div>
            @if(vp_current_user_can('create_users'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a class="btn btn-primary d-none d-md-block" href="{{route('admin.users.add')}}">{{__('a.New')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    {{--// SUPER ADMINS & ADMINS --}}
    @if($admins && $admins->count())
        <div class="tile">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table mb-5">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('a.Username')}}</th>
                                    <th scope="col">{{__('a.Display name')}}</th>
                                    <th scope="col">{{__('a.Role')}}</th>
                                    <th scope="col">{{__('a.Email')}}</th>
                                    <th scope="col" class="text-center">{{__('a.Blocked')}}</th>
                                    <th scope="col" class="text-center">{{__('a.Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($admins as $user)
                                    @php
                                        /**@var App\Models\User $user*/
                                        $isUserAdmin = vp_user_can($user, 'administrator');
                                        $isUserSuperAdmin = $user->isInRole([\App\Models\Role::ROLE_SUPER_ADMIN]);
                                        $isOwnProfile = ($current_user->getAuthIdentifier() == $user->id);
                                    @endphp
                                    <tr>
                                        <td class="text-primary">{{ $user->name }}</td>
                                        <td>{{ $user->display_name }}</td>
                                        <td class="text-info">{{ $user->role->display_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">{{ $user->is_blocked ? __('a.Yes') : __('a.No') }}</td>
                                        <td class="text-center">

                                            @if($isAuthUserSuperAdmin)
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-primary mr-1">{{__('a.Edit')}}</a>
                                                @if(!$isOwnProfile)
                                                    <a href="{{ route('admin.users.delete', $user->id) }}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                                       class="text-danger mr-1">{{__('a.Delete')}}</a>
                                                    @if($user->is_blocked)
                                                        <a href="{{ route('admin.users.unblock', $user->id) }}" class="text-primary">{{__('a.Unblock')}}</a>
                                                    @else
                                                        <a href="{{ route('admin.users.block', $user->id) }}" class="text-danger">{{__('a.Block')}}</a>
                                                    @endif
                                                @endif
                                            @elseif($isAuthUserAdmin && ! $isUserSuperAdmin)
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-primary mr-1">{{__('a.Edit')}}</a>
                                                @if(!$isOwnProfile)
                                                    <a href="{{ route('admin.users.delete', $user->id) }}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                                       class="text-danger mr-1">{{__('a.Delete')}}</a>
                                                    @if($user->is_blocked)
                                                        <a href="{{ route('admin.users.unblock', $user->id) }}" class="text-primary">{{__('a.Unblock')}}</a>
                                                    @else
                                                        <a href="{{ route('admin.users.block', $user->id) }}" class="text-danger">{{__('a.Block')}}</a>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--// REST OF USERS --}}
    @if($users && $users->count())
        <div class="tile">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table mb-5">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('a.Username')}}</th>
                                    <th scope="col">{{__('a.Display name')}}</th>
                                    <th scope="col">{{__('a.Role')}}</th>
                                    <th scope="col">{{__('a.Email')}}</th>
                                    <th scope="col" class="text-center">{{__('a.Blocked')}}</th>
                                    <th scope="col" class="text-center">{{__('a.Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        /**@var App\Models\User $user*/
                                        $isUserAdmin = vp_user_can($user, 'administrator');
                                        $isUserSuperAdmin = $user->isInRole([\App\Models\Role::ROLE_SUPER_ADMIN]);
                                        $isOwnProfile = ($current_user->getAuthIdentifier() == $user->id);
                                    @endphp
                                    <tr>
                                        <td class="text-primary">{{ $user->name }}</td>
                                        <td>{{ $user->display_name }}</td>
                                        <td class="text-info">{{ $user->role->display_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">{{ $user->is_blocked ? __('a.Yes') : __('a.No') }}</td>
                                        <td class="text-center">

                                            @if($isAuthUserSuperAdmin)
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-primary mr-1">{{__('a.Edit')}}</a>
                                                @if(!$isOwnProfile)
                                                    <a href="{{ route('admin.users.delete', $user->id) }}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                                       class="text-danger mr-1">{{__('a.Delete')}}</a>
                                                    @if($user->is_blocked)
                                                        <a href="{{ route('admin.users.unblock', $user->id) }}" class="text-primary">{{__('a.Unblock')}}</a>
                                                    @else
                                                        <a href="{{ route('admin.users.block', $user->id) }}" class="text-danger">{{__('a.Block')}}</a>
                                                    @endif
                                                @endif
                                            @elseif($isAuthUserAdmin && ! $isUserSuperAdmin)
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-primary mr-1">{{__('a.Edit')}}</a>
                                                @if(!$isOwnProfile)
                                                    <a href="{{ route('admin.users.delete', $user->id) }}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                                       class="text-danger mr-1">{{__('a.Delete')}}</a>
                                                    @if($user->is_blocked)
                                                        <a href="{{ route('admin.users.unblock', $user->id) }}" class="text-primary">{{__('a.Unblock')}}</a>
                                                    @else
                                                        <a href="{{ route('admin.users.block', $user->id) }}" class="text-danger">{{__('a.Block')}}</a>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="bs-component">
                                                <div class="alert alert-info">
                                                    {{__('a.No users found.')}}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Render pagination --}}
                    {{ $users->render() }}
                </div>
            </div>
        </div>
    @endif

@endsection
