@php
    $authUser = vp_get_current_user();
    $canAddUsers = $authUser->can('create_users');
    $canEditUsers = $authUser->can('edit_users');
    $canDeleteUsers = $authUser->can('delete_users');
    $canBlockUsers = $authUser->can('block_users');
    $isAuthUserSuperAdmin = $authUser->isInRole([\App\Models\Role::ROLE_SUPER_ADMIN]);
@endphp
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Users')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Users')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a href="{{route('admin.users.all')}}" class="btn btn-primary">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>


    @include('admin.partials.notices')

    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <div class="card-body">
                    <h4 class="tile-title">{{__('a.Create User')}}</h4>
                    @if($canAddUsers)
                        <form method="post" action="{{ route('admin.users.insert') }}">
                            <div class="form-group">
                                <label for="name">{{__('a.Name')}}</label>
                                <input type="text" class="form-control" name="name" value="{{old('name')}}" id="name" placeholder="{{__('a.Name')}}"/>
                            </div>
                            <div class="form-group">
                                <label for="display_name">{{__('a.Display name')}}</label>
                                <input type="text" class="form-control" name="display_name" value="{{old('display_name')}}" id="display_name" placeholder="{{__('a.Display name')}}"/>
                            </div>
                            <div class="form-group">
                                <label for="email">{{__('a.Email')}}</label>
                                <input type="email" class="form-control" name="email" value="{{old('email')}}" id="email" placeholder="{{__('a.Email')}}" autocomplete="off"/>
                            </div>
                            <div class="form-group">
                                <label for="password">{{__('a.Password')}}</label>
                                <input type="password" class="form-control" name="password" value="{{old('password')}}" id="password" autocomplete="off"/>
                            </div>
                            @if(vp_current_user_can('promote_users'))
                                <div class="form-group">
                                    <label for="role">{{__('a.Role')}}</label>
                                    <select class="form-control" name="role" id="role">
                                        @foreach($roles as $role)
                                            @if(!$isAuthUserSuperAdmin && ($role->name == \App\Models\Role::ROLE_SUPER_ADMIN))
                                                @continue
                                            @endif
                                            @php $selected = ($role->id == $default_role_id ? 'selected="selected"' : ''); @endphp
                                            <option value="{{ $role->id }}" {!! $selected !!}>
                                                {{ $role->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary mr-2">{{__('a.Add')}}</button>
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="tile">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
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
                                <tr>
                                    <td class="text-primary">{{ $user->name }}</td>
                                    <td>{{ $user->display_name }}</td>
                                    <td class="text-info">{{ $user->role->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-center">{{ $user->is_blocked ? __('a.Yes') : __('a.No') }}</td>
                                    <td class="text-center">
                                        @if($canEditUsers)
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-primary mr-1">{{__('a.Edit')}}</a>
                                        @endif

                                        @if($canDeleteUsers)
                                            <a href="{{ route('admin.users.delete', $user->id) }}"
                                               data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                               class="text-danger mr-1">{{__('a.Delete')}}</a>
                                        @endif

                                        @if($canBlockUsers)
                                            @if($user->is_blocked)
                                                <a href="{{ route('admin.users.unblock', $user->id) }}" class="text-primary">{{__('a.Unblock')}}</a>
                                            @else
                                                <a href="{{ route('admin.users.block', $user->id) }}" class="text-danger">{{__('a.Block')}}</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-info">
                                            {{__('a.No users found.')}}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $users->render() }}
                </div>
            </div>
        </div>
    </div>
@endsection
