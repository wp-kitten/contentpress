<footer class="footer app-footer">
    <div class="d-sm-flex justify-content-center justify-content-sm-between">
        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">{{__('a.Copyright')}} &copy; {{ \Carbon\Carbon::now()->format('Y') }} <a href="{{config('app.url')}}" target="_blank">{{config('app.name')}}</a> {{__('a.All rights reserved.')}}</span>
        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"><a href="https://valpress.net" target="_blank">ValPress</a> v{{vp_get_app_version()}}</span>
    </div>
</footer>
