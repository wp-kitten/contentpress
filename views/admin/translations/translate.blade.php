@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Translate')}}</title>
@endsection

@section('main')

    <div class="row">
        <div class="col-md-12">
            <div class="card bg-white">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <h4 class="mt-1 mb-1">{{__('a.Translate')}}</h4>
                    {{-- Especially useful when changing themes, since they come with their own texts --}}
                    <form method="post" action="{{route('admin.translations.fn.sync', ['fn' => request('fn')])}}" class="form-inline">
                        <button type="submit" class="btn btn-primary ml-auto" title="{{__('a.Sync translation')}}">
                            {{__('a.Sync')}}
                        </button>
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        @php
            $cssClass = ( (request('fn') == \App\Helpers\TranslationManager::LANG_FILE_ADMIN) ? 'text-primary' : 'text-muted' );
        @endphp
        <div class="col-md-3 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title {{$cssClass}}">{{__('a.Admin Dashboard')}}</h4>
                    <a href="{{route('admin.translations.fn', ['fn' => \App\Helpers\TranslationManager::LANG_FILE_ADMIN])}}" class="{{$cssClass}}">
                        <span class="mdi mdi-view-dashboard"></span>
                        <span>{{__('a.Admin Dashboard')}}</span>
                    </a>
                </div>
            </div>
        </div>

        @php
            $cssClass = ( (request('fn') == \App\Helpers\TranslationManager::LANG_FILE_FRONTEND) ? 'text-primary' : 'text-muted' );
        @endphp
        <div class="col-md-3 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title {{$cssClass}}">{{__('a.Frontend')}}</h4>
                    <a href="{{route('admin.translations.fn', ['fn' => \App\Helpers\TranslationManager::LANG_FILE_FRONTEND])}}" class="{{$cssClass}}">
                        <span class="mdi mdi-sitemap"></span>
                        <span>{{__('a.Frontend')}}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    {{-- END .row --}}

    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title">{{__('a.Localized strings')}}</h4>

                    <form id="form-translations" method="post" action="{{route('admin.translations.fn.update', ['fn' => request('fn')])}}">
                        @forelse($strings as $index => $string)
                            <div class="form-group">
                                <label for="{{$index}}-field">{{$string}}</label>
                                <input class="form-control" id="{{$index}}-field" name="translation_fields[{{$index}}]" type="text" value="{{$string}}"/>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                {{__('a.No localized texts were found. maybe the path to the views directory is not correct.')}}
                            </div>
                        @endforelse

                        <input type="hidden" name="input_current_language" value="{{cp_get_user_meta('backend_user_current_language')}}"/>
                        <input type="hidden" name="input_current_fn" value="{{request('fn')}}"/>
                        <button type="submit" class="btn btn-success ml-2" title="{{__('a.Save translation')}}">{{__('a.Save')}}</button>
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- END .row --}}

@endsection
