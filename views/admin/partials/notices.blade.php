@if( session('message') || $errors->any() )
    @if( session('message'))
        <div class="bs-component">
            <div class="alert alert-{{session('message.class')}}">
                <div>{!! wp_kses_post(session('message.text')) !!}</div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="bs-component">
                <div class="alert alert-danger">
                    <div>{!! wp_kses_post($error) !!}</div>
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
                <div class="user-notice-content">{!! wp_kses_post($notice['content']) !!}</div>
            </div>
        </div>
    @endforeach
@endif
