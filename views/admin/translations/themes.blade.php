@inject('languageClass', App\Models\Language)
@extends('admin.layouts.base')

@php
    /**@var \App\Helpers\TranslationManager $translations_manager*/
@endphp

@section('page-title')
    <title>{{__('a.Translations')}}</title>
@endsection

@section('footer-scripts')
    <script>
        jQuery( function ($) {
            "use strict";
            var baseUrl = "{{route('admin.translations.themes')}}";
            $( '#select-theme' ).on( 'change', function (ev) {
                // window.location.href = baseUrl + '?dir=' + $( this ).val();
                $( '#form-select-theme' ).trigger( 'submit' );
            } );
        } );
    </script>
@endsection



@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Translations - Themes')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        @if($has_themes)
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="tile-title mb-3">{{__('a.Languages')}}</h4>

                        {{-- Select Theme --}}
                        @if(empty($edited_dir))
                            <form id="form-select-theme" method="get" action="{{route('admin.translations.themes')}}">
                                <label for="select-theme">{{__("a.Select theme")}}</label>
                                <select name="dir" id="select-theme" class="form-control">
                                    <option value="">{{__("a.Select")}}</option>
                                    @foreach($themes as $themeDir)
                                        @php $theme = new \App\Helpers\Theme($themeDir); @endphp
                                        <option value="{{$themeDir}}">{{$theme->get('display_name')}}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            {{-- Select language --}}
                            <div id="languages_accordion">
                                @foreach($enabled_languages as $langCode)
                                    {{-- Skip the default language --}}
                                    @if($langCode == $default_language_code)
                                        @continue
                                    @endif

                                    @php
                                        //#! Make sure the language directory exists
                                        $langDir = $translations_manager->getLanguagesDirPath($langCode, VALPRESS_TYPE_THEME, $edited_dir);
                                    @endphp
                                    @if( ! \Illuminate\Support\Facades\File::isDirectory($langDir))
                                        <p class="text-description">{{__("a.The directory for the :language language was not found.", ['language' => $languageClass->getNameFrom($langCode)])}}</p>
                                        <div>
                                            <a href="#"
                                               onclick="event.preventDefault(); document.getElementById('form-create-translation').submit();"
                                               class="btn btn-primary btn-sm">
                                                {{__("a.Create translation")}}
                                            </a>
                                            <form id="form-create-translation" method="post" action="{{route('admin.translations.themes.create')}}">
                                                @csrf
                                                <input type="hidden" name="lang_code" value="{{$langCode}}"/>
                                                <input type="hidden" name="theme_dir_name" value="{{$edited_dir}}"/>
                                            </form>
                                        </div>
                                        @continue
                                    @endif

                                    <div class="card">
                                        <div id="heading-{{$langCode}}" class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link text-left pl-0"
                                                        data-toggle="collapse"
                                                        data-target="#collapse-{{$langCode}}"
                                                        aria-expanded="true"
                                                        aria-controls="collapse-{{$langCode}}">
                                                    {{$languageClass->getNameFrom($langCode)}}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapse-{{$langCode}}"
                                             class="collapse @if($langCode == $edited_language_code) show fade @endif"
                                             aria-labelledby="heading-{{$langCode}}"
                                             data-parent="#languages_accordion">
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    @php
                                                        $files = $translations_manager->getFiles($langCode, VALPRESS_TYPE_THEME, $edited_dir);
                                                    @endphp
                                                    @if(empty($files))

                                                    @else

                                                    @endif
                                                    @foreach($files as $splFileInfo)
                                                        @php
                                                            $fn = $splFileInfo->getFilename();
                                                            $activeClass = '';
                                                            if($langCode == $edited_language_code){
                                                                if($fn == $edited_language_file){
                                                                    $activeClass = 'text-warning';
                                                                }
                                                            }
                                                        @endphp
                                                        <li>
                                                            <a href="{{route('admin.translations.themes', [
                                                                    'type' => VALPRESS_TYPE_THEME,
                                                                    'code' => $langCode,
                                                                    'dir' => $edited_dir,
                                                                    'fn'=> $fn
                                                                ])}}" class="{{$activeClass}}">
                                                                {{$fn}}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="m-0">
                                                    <p class="text-description mb-0">
                                                        {{__('a.Click on any of the above files to translate.')}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(! empty($edited_type) && ! empty($edited_language_code) && ! empty($edited_language_file))
                @php
                    $dirPath = $translations_manager->getLanguagesDirPath($edited_language_code, $edited_type, $edited_dir);
                    $fileData = \Illuminate\Support\Facades\File::get(trailingslashit($dirPath).$edited_language_file);
                @endphp
                <div class="col-sm-12 col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="tile-title mb-3">{{__('a.Edit language file :name', ['name' => $edited_dir.'/'.$edited_language_code.'/'.$edited_language_file])}}</h4>

                            <div class="bs-component">
                                <div class="alert alert-warning">
                                    <span>{{__("a.Be very careful when editing this content because a missing ',' or any other invalid PHP code might crash your website and you will be forced to fix the file via FTP!")}}</span>
                                </div>
                            </div>

                            <div class="form-wrap">
                                <form method="post" action="{{route('admin.translations.update')}}">
                                    @csrf
                                    <input type="hidden" name="language_file" value="{{$edited_language_file}}"/>
                                    <input type="hidden" name="dir_name" value="{{$edited_dir}}"/>
                                    <input type="hidden" name="type" value="{{$edited_type}}"/>
                                    <input type="hidden" name="lang_code" value="{{$edited_language_code}}"/>

                                    <div class="form-group">
                                        <label for="file_data"></label>
                                        <textarea id="file_data" name="file_data" class="form-control" style="font-size: 1.1rem" rows="30">{!! $fileData !!}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">{{__('a.Update')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="col-sm-12">
                <div class="bs-component">
                    <div class="alert alert-warning">
                        <span>{{__("a.No themes found.")}}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
