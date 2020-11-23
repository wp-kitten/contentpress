@inject('languageClass', App\Models\Language)

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Languages')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Languages')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            {{-- ENABLED LANGUAGES --}}
            <div class="col-sm-12 col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Enabled languages')}}</h3>
                    <form class="" method="post" action="{{route('admin.settings.languages.update')}}">
                        <div class="form-group">

                            @foreach($languages as $lang)
                                @php $checked = ''; $disabled = ''; $text = ''; @endphp

                                @foreach($enabled_languages as $langCode)
                                    @if($langCode == $lang->code)
                                        @php $checked = 'checked="checked"'; @endphp
                                        @break
                                    @endif
                                @endforeach

                                {{-- Disable the default language since it must be present in the request --}}
                                @if($lang->code == $default_language_code)
                                    @php
                                        $disabled = 'disabled="disabled"';
                                        $text = __('a.(This is the default language)');
                                    @endphp
                                @endif

                                <div class="animated-checkbox">
                                    <label for="language-field-{{$lang->code}}">
                                        <input type="checkbox"
                                               id="language-field-{{$lang->code}}"
                                               name="selected_languages[]"
                                               value="{{$lang->code}}" {!! $checked !!} {!! $disabled !!}/>
                                        <span class="label-text"> {{$lang->name}}</span>
                                        @if(! empty($text))
                                            <span class="text-description d-inline">{{$text}}</span>
                                        @else
                                            <a href="#"
                                               class="ml-3 font-weight-bold text-danger"
                                               data-form-id="form-delete-language-{{$lang->id}}"
                                               data-confirm="{{__('a.Are you sure you want to delete this language?')}}"
                                               title="{{__('a.Delete')}}">&times;</a>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            {{__('a.Save')}}
                        </button>

                        @csrf
                    </form>

                    {{-- RENDER DELETE FORMS --}}
                    @foreach($languages as $lang)
                        @if($lang->code != $default_language_code)
                            <form method="post"
                                  action="{{route('admin.settings.languages.delete', $lang->id)}}"
                                  id="form-delete-language-{{$lang->id}}">
                                @csrf
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- ADD NEW LANGUAGES --}}
            <div class="col-sm-12 col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Add new language')}}</h3>
                    <form class="" method="post" action="{{route('admin.settings.languages.add')}}">
                        @csrf

                        <div class="form-group">
                            <label for="language_code">{{__('a.Language code')}}</label>
                            <input type="text" id="language_code" name="language_code" maxlength="2" class="form-control" value="{{old('language_code')}}" placeholder="ro"/>
                        </div>
                        <div class="form-group">
                            <label for="language_name">{{__('a.Language name')}}</label>
                            <input type="text" id="language_name" name="language_name" maxlength="50" class="form-control" value="{{old('language_name')}}" placeholder="Romanian"/>
                        </div>

                        <div class="form-group">
                            <p class="text-description">
                                {!! __('You can find all the languages <a href=":url" target="_blank" title="Opens in a new tab/window">here</a>. Just grab the name from the <strong>ISO language name</strong> column and the code from the <strong>639-1</strong> column.', ['url' => 'https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes']) !!}
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            {{__('a.Add')}}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
