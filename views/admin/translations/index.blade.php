@inject('languageClass', 'App\Models\Language')
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Translations')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Translations - Core')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">

        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title mb-3">{{__('a.Languages')}}</h4>

                    {{-- Languages Accordion --}}
                    <div id="languages_accordion">
                        @foreach($enabled_languages as $langCode)
                            {{-- Skip the default language --}}
                            @if($langCode == $default_language_code)
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
                                                /**@var \App\Helpers\TranslationManager $translations_manager*/
                                                  $files = $translations_manager->getFiles($langCode, VALPRESS_TYPE_CORE);
                                            @endphp
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
                                                    <a href="{{route('admin.translations.core', [
                                                                    'type' => VALPRESS_TYPE_CORE,
                                                                    'code' => $langCode,
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
                </div>
            </div>
        </div>

        @if(! empty($edited_type) && ! empty($edited_language_code) && ! empty($edited_language_file))
            @php
                $dirPath = $translations_manager->getLanguagesDirPath($edited_language_code, $edited_type, $edited_dir);
                $fileData = \Illuminate\Support\Facades\File::get($dirPath.'/'. $edited_language_file);
            @endphp
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-body">
                        <h4 class="tile-title mb-3">{{__('a.Edit language file :name', ['name' => $edited_language_code.'/'.$edited_language_file])}}</h4>

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
    </div>
@endsection
