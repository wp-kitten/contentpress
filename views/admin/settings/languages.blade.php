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
            <div class="col-md-6">
                <div class="tile">
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
                </div>
            </div>
        </div>
    @endif
@endsection
