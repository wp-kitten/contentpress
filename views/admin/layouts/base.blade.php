@php
    $locale = cp_get_user_meta( 'backend_user_current_language' );
    if( empty( $locale ) ) {
        $locale = App\Helpers\CPML::getDefaultLanguageCode();
    }
    app()->setLocale($locale);
@endphp<!doctype html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    @hasSection('page-title')
        @yield('page-title')
    @else
        <title>{{__('a.Admin Dashboard')}}</title>
    @endif

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.gstatic.com"/>
    <link href="//fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400;1,700;1,900&display=swap" rel="stylesheet"/>
    <link href="//fonts.googleapis.com/css2?family=Niconne&display=swap" rel="stylesheet"/>
    {{--    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>--}}

    {{-- Selectize.js --}}
    <link href="{{asset('vendor/selectize.js/dist/css/selectize.css')}}" rel="stylesheet" type="text/css"/>

    {{-- Quill --}}
    <link href="{{asset('vendor/quill/quill.core.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('vendor/quill/quill.bubble.css')}}" rel="stylesheet" type="text/css"/>

    {{-- Styles --}}
    <link href="{{ asset('vendor/jquery.toast/jquery.toast.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('vendor/flag-icon-css/css/flag-icon.min.css') }}" rel="stylesheet" type="text/css"/>

    <link href="{{asset('_admin/css/main.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('_admin/css/admin-helpers.css')}}" rel="stylesheet" type="text/css"/>

    {{-- Scripts --}}
    <script src="//kit.fontawesome.com/cec4674fec.js"></script>
    <script src="{{ asset('_admin/js/app-dependencies.js') }}"></script>
    <script src="{{asset('vendor/jquery.toast/jquery.toast.min.js')}}"></script>

    {{-- Localized data --}}
    <script id="app-locale">
        window.AppLocale = {
            nonce_name: '_token',
            nonce_value: "{{ csrf_token() }}",
            user_id: "{{auth()->user()->getAuthIdentifier()}}",

            ajax: {
                url: "{{route('admin.ajax')}}",
                login_url: "{{route('login')}}",
                loader_url: "{{asset('img/ajax-loader.svg')}}",
                type_success: "{{TYPE_SUCCESS}}",
                type_error: "{{TYPE_ERROR}}",
                empty_response: "{{__('a.Empty response from server.')}}",
                no_response: "{{__('a.No response from server.')}}",
                unexpected_response: "{{__('a.Unexpected response from server.')}}",
                error_response: "{{__('a.An error occurred.')}}",
            }
        };
    </script>

    <script>
        var resetToastPosition = function () {
            //#! To remove previous position style
            $( '.jq-toast-wrap' ).removeClass( 'bottom-left bottom-right top-left top-right mid-center' );
            $( ".jq-toast-wrap" ).css( {
                "top": "",
                "left": "",
                "bottom": "",
                "right": ""
            } );
        };

        /**
         * Show a toast message
         * @param text
         * @param icon (see https://kamranahmed.info/toast#toasts-icons)
         * @param title The title for the toast
         * @param position (see https://kamranahmed.info/toast#toasts-positioning)
         * @param allowClose Whether or not to display the close button
         * @param hideAfter The number of seconds to hide the toast message after
         */
        function showToast(text, icon = 'info', title = '', position = 'bottom-right', allowClose = true, hideAfter = 5) {
            resetToastPosition();
            $.toast( {
                heading: title,
                text: text,
                position: position,
                //#! The max allowed number of toast messages to be displayed (ex: in case another toast message should be displayed before the previous one closes)
                stack: 3,
                icon: icon,
                allowToastClose: allowClose,
                // in milliseconds
                hideAfter: ~~hideAfter * 1000,
            } );
            return false;
        }
    </script>

    {{cp_admin_head()}}
    @yield('head-scripts')
</head>
<body class="app sidebar-mini">

    <div class="app-wrapper">
        @include('admin.layouts.partials.navbar')

        @include('admin.layouts.partials.sidebar')

        <div class="app-content custom-scroll">
            <div class="app-content-wrapper">
                @yield('main')
            </div>
            @include('admin.layouts.partials.footer')
        </div><!-- // main-panel -->
    </div>

    <script src="{{asset('_admin/js/admin-heartbeat.js')}}"></script>
    <script src="{{asset('_admin/js/ContentPressTextEditor.js')}}"></script>
    <script src="{{asset('vendor/selectize.js/dist/js/standalone/selectize.min.js')}}"></script>
    <script src="{{asset('vendor/quill/quill.min.js')}}"></script>
    <script src="{{asset('vendor/admin-template/popper.min.js')}}"></script>
    <script src="{{asset('vendor/bootstrap/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('vendor/admin-template/main.js')}}"></script>
    <script src="{{asset('vendor/admin-template/plugins/pace.min.js')}}"></script>
    <script src="{{asset('vendor/admin-template/plugins/chart.js')}}"></script>

    {{cp_admin_footer()}}
    @yield('footer-scripts')
</body>
</html>
