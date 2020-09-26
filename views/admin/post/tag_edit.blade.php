@php
    $languageClass = new App\Models\Language();
    $optionsClass = new App\Models\Options();
    $isMultiLanguage = (count($enabled_languages) > 1);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit Tag')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit tag')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route("admin.{$__post_type->name}.tag.all")}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>


    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_taxonomies'))
        <div class="row">

        <div class="col-md-6">
            <div class="tile">
                @if($isMultiLanguage)
                    <h3 class="tile-title">{{__('a.Default language')}}: {{$languageClass->getNameFrom($default_language_code)}}</h3>
                @endif

                <form method="post" action="{{route("admin.{$__post_type->name}.tag.update", ['id' => $tag->id])}}">

                    <div class="form-group">
                        <label for="name-field">{{__('a.Name')}}</label>
                        <input type="text" class="form-control" id="name-field" name="name" value="{{$tag->name}}" placeholder="{{__('a.Name')}}">
                    </div>

                    <button type="submit" class="btn btn-primary mr-2">{{__('a.Update')}}</button>
                    <a href="{{route("admin.{$__post_type->name}.tag.delete", ['id' => $tag->id])}}"
                       data-confirm="{{__('a.Are you sure you want to delete this tag?')}}"
                       class="btn btn-danger mr-2">{{__('a.Delete')}}</a>

                    <input type="hidden" name="language_id" value="{{$tag->language_id}}"/>
                    @csrf
                </form>
            </div>
        </div>

        @if($isMultiLanguage)
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Translations')}}</h3>

                    <div class="mt-4">
                        <div class="accordion" role="tablist" id="translations-accordion">
                            @php $shown = false @endphp
                            @foreach($enabled_languages as $languageCode)
                                {{-- we've already displayed this form above --}}
                                @if($languageCode == $default_language_code)
                                    @continue
                                @endif

                                {{-- The active panel class --}}
                                @php
                                    $showClass = 'show';
                                    if($shown){
                                        $showClass = '';
                                    }
                                @endphp

                                {{-- Get current entry data --}}
                                @php
                                    $currentLanguageID = $languageClass->getID($languageCode);
                                    $currentLanguageName = $languageClass->getNameFrom($languageCode);

                                    $transTag = App\Models\Tag::where('language_id', $currentLanguageID)
                                                    ->where('post_type_id', $__post_type->id)
                                                    ->where('translated_tag_id', $tag->id)
                                                    ->first();
                                    if($transTag){
                                        $currentLanguageID = $transTag->language_id;
                                        $currentLanguageName = $languageClass->getNameFrom($currentLanguageID);
                                    }
                                @endphp

                                <div class="card">

                                    <div class="card-header" role="tab" id="heading-{{$languageCode}}">
                                        <h6 class="mb-0">
                                            <a data-toggle="collapse" href="#collapse-{{$languageCode}}" aria-expanded="true" aria-controls="collapse-{{$languageCode}}" class="">
                                                {{$currentLanguageName}}
                                            </a>
                                        </h6>
                                    </div>
                                    <div id="collapse-{{$languageCode}}" class="collapse {{$showClass}}" role="tabpanel" aria-labelledby="heading-{{$languageCode}}" data-parent="#translations-accordion">
                                        <div class="card-body">
                                            <form method="post" action="{{route("admin.{$__post_type->name}.tag.translate", [
                                                    'language_id' => $currentLanguageID
                                                    ])}}">
                                                <div class="form-group">
                                                    <label for="name-field-{{$languageCode}}">{{__('a.Name')}}</label>
                                                    <input type="text" class="form-control" id="name-field-{{$languageCode}}" name="name"
                                                           value="{{$transTag ? $transTag->name : ''}}"
                                                           placeholder="{{__('a.Name')}}">
                                                </div>

                                                <button type="submit" class="btn btn-primary mr-2">{{__('a.Update')}}</button>
                                                @if($transTag)
                                                    <a href="{{route("admin.{$__post_type->name}.tag.delete", ['id' => $transTag->id])}}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this tag?')}}"
                                                       class="btn btn-danger mr-2">
                                                        {{__('a.Delete')}}
                                                    </a>
                                                @endif

                                                <input type="hidden" name="current_tag_id" value="{{$transTag ? $transTag->id : 0}}"/>
                                                <input type="hidden" name="translated_tag_id" value="{{$tag->id}}"/>
                                                @csrf
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reset show class --}}
                                @php
                                    $shown = true;
                                @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- end .row --}}
    @endif
@endsection
