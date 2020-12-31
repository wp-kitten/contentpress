@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Roles')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Roles')}}</h1>
            </div>
            @if(vp_current_user_can('promote_users'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a class="btn btn-primary d-none d-md-block" href="{{route('admin.roles.add')}}">{{__('a.New')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    {{--// SUPER ADMINS & ADMINS --}}
    @if($roles && $roles->count())
        <div class="tile">

            <div class="row">
                <div class="col-sm-12 col-md-3">
                    <div class="roles-wrap card">
                        <h3 class="card-header">{{__('a.Roles')}}</h3>
                        <div class="card-body">
                            @foreach($roles as $role)
                                <?php
                                    $isProtected = cp_is_role_protected( $role->name );
                                ?>
                                <div class="d-flex align-content-center align-items-center justify-content-start">
                                    @if($isProtected)
                                        <i class="fas fa-key text-danger mr-3" aria-hidden="true" title="{{__('a.Internal role')}}"></i>
                                        @else
                                        <i class="fas fa-user-tag text-primary mr-3" aria-hidden="true"></i>
                                    @endif
                                    <a href="{{route('admin.roles.edit', $role->id)}}" class="text-primary mr-3" title="{{__('a.Click to edit')}}">{{$role->display_name}}</a>
                                    @if(!$isProtected)
                                        <a href="#"
                                           title="{{__('a.Delete')}}"
                                           data-confirm="{{__('a.Are you absolutely sure you want to delete this role?')}}"
                                           data-form-id="form-{{$role->id}}"
                                           class="btn btn-danger btn-sm pt-0 pb-0 pl-2 pr-2">&times;</a>
                                        <form id="form-{{$role->id}}" class="hidden" method="post" action="{{route('admin.roles.delete', $role->id)}}">
                                            @csrf
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

@endsection
