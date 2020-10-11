@if( session('message') || $errors->any() )
    @if( session('message'))
        <div class="bs-component">
            <div class="alert alert-{{session('message.class')}}">
                <span>{{session('message.text')}}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="bs-component">
                <div class="alert alert-danger">
                    <span>{{$error}}</span>
                </div>
            </div>
        @endforeach
    @endif
@endif

@php
    $notices = App\Helpers\UserNotices::getInstance();
    $userNotices = $notices->getAll();
    $notices->removeAll();
@endphp
@if($userNotices)
    @foreach($userNotices as $notice)
        <div class="bs-component">
            <div class="alert alert-{{$notice['type']}} alert-dismissible">
                <button class="close" type="button" data-dismiss="alert" aria-label="{{__('a.Close')}}">&times;</button>
                <div class="user-notice-content">{!! $notice['content'] !!}</div>
            </div>
        </div>
    @endforeach
@endif
