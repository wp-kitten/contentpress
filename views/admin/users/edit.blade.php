@php
    /**@var \Illuminate\Auth\Authenticatable|\App\Models\User $auth_user*/
    /**@var \App\Models\User $user*/
    $isOwnProfile = ($user->id == $auth_user->getAuthIdentifier());
    $userImageUrl = cp_get_user_profile_image_url($user->id);
    $isAuthUserSuperAdmin = $auth_user->isInRole([\App\Models\Role::ROLE_SUPER_ADMIN]);
    $isAuthUserAdmin = $auth_user->isInRole([\App\Models\Role::ROLE_ADMIN]);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Users')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>
                    @if($isOwnProfile)
                        {{__('a.Your profile')}}
                    @else
                        {{__('a.User profile')}}
                    @endif
                </h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    {{--// Make sure the current user can edit super admin roles --}}
    @if(!$isOwnProfile && !$auth_user->can( 'edit_users' ))
        <div class="bs-component">
            <div class="alert alert-warning">
                {{__('a.You are not allowed to perform this action.')}}
            </div>
        </div>
    {{--// If edited user is super admin then the current user must be super admin as well --}}
    @elseif($user->isInRole( [ \App\Models\Role::ROLE_SUPER_ADMIN ] ) && ! $isAuthUserSuperAdmin)
        <div class="bs-component">
            <div class="alert alert-warning">
                {{__('a.You are not allowed to perform this action.')}}
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-4">
                <div class="tile">
                    <div class="card-body">
                        <h4 class="tile-title">
                            @if($isOwnProfile)
                                {{__('a.Edit your profile')}}
                            @else
                                {{__('a.Edit user profile')}}
                            @endif
                        </h4>
                        <form method="post" action="{{ route('admin.users.update') }}">
                            <div class="form-group">
                                <label for="name">{{__('a.Name')}}</label>
                                <input type="text" class="form-control" name="name" value="{{ $user->name }}" id="name" placeholder="{{__('a.Name')}}" required/>
                            </div>
                            <div class="form-group">
                                <label for="display_name">{{__('a.Display name')}}</label>
                                <input type="text" class="form-control" name="display_name" value="{{ $user->display_name }}" id="display_name" placeholder="{{__('a.Display name')}}" required/>
                            </div>
                            <div class="form-group">
                                <label for="email">{{__('a.Email')}}</label>
                                <input type="email" class="form-control" name="email" value="{{ $user->email }}" id="email" placeholder="{{__('a.Email')}}" autocomplete="off" required/>
                            </div>
                            <div class="form-group">
                                <label for="password">{{__('a.Password')}}</label>
                                <input type="password" class="form-control" name="password" value="" id="password" autocomplete="off"/>
                            </div>
                            <div class="form-group">
                                <label for="role">{{__('a.Role')}}</label>
                                @if(cp_current_user_can('promote_users'))
                                    <select class="form-control border" name="role" id="role">
                                        {{--// Administrators cannot promote users to super admin role --}}
                                        @foreach($roles as $role)
                                            @if(!$isAuthUserSuperAdmin && ($role->name == \App\Models\Role::ROLE_SUPER_ADMIN))
                                                @continue
                                            @endif

                                            @php $selected = ($role->id == $user->role->id ? 'selected="selected"' : ''); @endphp
                                            <option value="{{ $role->id }}" {!! $selected !!}>{{ $role->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <p class="">{{$user->role->display_name}}</p>
                                @endif
                            </div>

                            @if(cp_current_user_can('block_users'))
                                @if( ! $isOwnProfile && ($isAuthUserSuperAdmin || $isAuthUserAdmin))
                                    <div class="form-group">
                                        <label for="blocked">{{__('a.Blocked') }}</label>
                                        <select class="form-control border" name="blocked" id="blocked">
                                            <option value="1" @if($user->is_blocked) selected @endif>{{__('a.Yes')}}
                                            </option>
                                            <option value="0" @if(!$user->is_blocked) selected @endif>{{__('a.No')}}
                                            </option>
                                        </select>
                                    </div>
                                @endif
                            @endif

                            <button type="submit" class="btn btn-primary mr-2">{{__('a.Update')}}</button>
                            <button type="button" class="btn btn-danger"
                                    data-confirm="{{__('a.Are you sure you want to delete this user? All items associated with it will also be deleted.')}}"
                                    data-url="{{ route('admin.users.delete', $user->id) }}">{{__('a.Delete')}}</button>

                            <input type="hidden" name="user_id" value="{{$user->id}}"/>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="tile">
                    <div class="card-body">
                        <h4 class="tile-title">
                            {{__('a.Account Management') }}
                        </h4>

                        <form id="user-profile-form"
                              method="post"
                              @if(cp_current_user_can( 'upload_files' )) enctype="multipart/form-data" @endif
                              action="{{route('admin.users.update_profile', ['id' => $user->id])}}">

                            <div class="form-group row">
                                <label for="field-website" class="col-sm-3 col-form-label">{{__('a.Website')}}</label>
                                <div class="col-sm-9">
                                    @php
                                        $websiteUrl = cp_get_user_meta('_website_url', $user->id);
                                    @endphp
                                    <input type="url"
                                           class="form-control"
                                           id="field-website"
                                           name="user_profile_website"
                                           placeholder="{{__('a.Website')}}"
                                           value="{{$websiteUrl}}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="field-bio" class="col-sm-3 col-form-label">{{__('a.Biographical Info')}}</label>
                                <div class="col-sm-9">
                                    @php
                                        $userBio = cp_get_user_meta('_user_bio', $user->id);
                                    @endphp
                                    <div class="quill-scrolling-container">
                                        <div id="field-bio-editor">{!! $userBio !!}</div>
                                        <textarea
                                            class="form-control hidden"
                                            id="field-bio"
                                            name="user_profile_bio"
                                            placeholder="{{__('a.Biographical Info')}}">{!! $userBio !!}</textarea>
                                    </div>

                                </div>
                            </div>

                            @if(cp_current_user_can( 'upload_files' ))
                                <div class="form-group row">
                                    <label for="field-website" class="col-sm-3 col-form-label">{{__('a.Profile picture')}}</label>
                                    <div class="col-sm-9">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="user_image_field-wrapper">
                                                    <input type="file" id="user_image_field" accept="image/*" class="dropify" data-default-file="{{$userImageUrl}}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button type="submit" id="js-acc-mgmt-update-btn" class="btn btn-primary mr-2">{{__('a.Update')}}</button>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

        </div>{{-- End .row --}}

        {{-- @requires capability: manage_custom_fields --}}
        @include('admin.partials.meta-fields', [
            'meta_fields' => $meta_fields,
            'model' => App\Models\UserMeta::class,
            'language_id' => App\Helpers\CPML::getDefaultLanguageID(),
            'fk_name' => 'user_id',
            'fk_value' => $user->id,
        ])
    @endif
@endsection
