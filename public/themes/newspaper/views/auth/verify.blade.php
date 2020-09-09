@extends('layouts.frontend')

@section('title')
    <title>{{esc_html(__('np::m.Verify Email'))}}</title>
@endsection

@section('content')

    <main class="site-page page-verify">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('np::m.Verify Your Email Address') }}</div>

                        <div class="card-body">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('np::m.A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            {{ __('np::m.Before proceeding, please check your email for a verification link.') }}
                            {{ __('np::m.If you did not receive the email') }},
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('np::m.click here to request another') }}</button>
                                .
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection
