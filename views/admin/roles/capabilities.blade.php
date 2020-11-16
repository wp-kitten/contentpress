@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Capabilities')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Capabilities')}}</h1>
                <p class="text-description mt-2">{{__("a.Capabilities should be created from code since there is the only place they're used.")}}</p>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="tile">
        <form method="post" action="{{route('admin.roles.capabilities.update')}}">
            @csrf
            <table class="table table-responsive-md table-striped">
                <thead>
                    <tr>
                        <th scope="col" class="p-1">{{__('a.Capability')}}</th>
                        @foreach($roles as $role)
                            <th scope="col" class="p-1">{{$role->display_name}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($capabilities as $cap)
                        <tr>
                            <td class="p-1">
                                <p class="m-0">{{$cap->name}}</p>
                                <p class="m-0 text-description">{{$cap->description}}</p>
                            </td>
                            @foreach($roles as $role)
                                <td class="p-1">
                                    <?php
                                    $inputID = "cap-{$role->id}-{$cap->id}";
                                    $inputName = 'capabilities['.$role->id.']['.$cap->id.']';
                                    $checked = ( $role->hasCap( $cap->id ) ? 'checked="checked"' : '' );
                                    $disabled = ( $role->name == 'super_admin' ? 'disabled="disabled"' : '' );
                                    ?>
                                    <label for="{{$inputID}}"></label>
                                    <input id="{{$inputID}}" name="{{$inputName}}" type="checkbox" value="1" {!! $checked !!} {!! $disabled !!}/>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">{{__('a.Update')}}</button>
            </div>
        </form>
    </div>
@endsection
