@php
    $languageClass = new App\Models\Language();
    $optionsClass = new App\Models\Options();
    $isMultiLanguage = (count($enabled_languages) > 1);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit Post Type')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit Post Type')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route('admin.settings.post_types')}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('manage_options'))
        <div class="row">

            <div class="col-md-6">
                <div class="tile">
                    @if($isMultiLanguage)
                        <h4 class="tile-title">{{__('a.Default language')}}
                            : {{$languageClass->getNameFrom($default_language_code)}}</h4>
                    @endif

                    {{-- GET THE CUSTOM OPTIONS --}}
                    @php
                        $optName = "post_type_{$entry->name}_allow_categories";
                        $allowCategories = $optionsClass->getOption($optName, true);
                        $allowCategoriesChecked = ($allowCategories ? 'checked="checked"' : '');

                        $optName = "post_type_{$entry->name}_allow_tags";
                        $allowTags = $optionsClass->getOption($optName, true);
                        $allowTagsChecked = ($allowTags ? 'checked="checked"' : '');

                        $optName = "post_type_{$entry->name}_allow_comments";
                        $allowComments = $optionsClass->getOption($optName, true);
                        $allowCommentsChecked = ($allowComments ? 'checked="checked"' : '');
                    @endphp

                    <div class="table-responsive">
                        <form method="post" action="{{route('admin.settings.post_types.update', ['id' => $entry->id])}}">

                            <div class="form-group">
                                <label for="name-field">{{__('a.Name')}}</label>
                                <input type="text" class="form-control" id="name-field" name="name" value="{{$entry->name}}" placeholder="{{__('a.Name')}}">
                            </div>

                            <div class="form-group">
                                <label for="display_name-field">{{__('a.Display name')}}</label>
                                <input type="text" class="form-control" id="display_name-field" name="display_name" value="{{$entry->display_name}}" placeholder="{{__('a.Display name')}}">
                            </div>

                            <div class="form-group">
                                <label for="plural_name-field">{{__('a.Plural name')}}</label>
                                <input type="text" class="form-control" id="plural_name-field" name="plural_name" value="{{$entry->plural_name}}" placeholder="{{__('a.Plural name')}}">
                            </div>

                            <div class="animated-checkbox">
                                <label for="chk-{{$entry->id}}-allow_categories">
                                    <input type="checkbox" id="chk-{{$entry->id}}-allow_categories" name="allow_categories" class="form-check-input"
                                           value="{{$allowCategories}}"
                                        {!! $allowCategoriesChecked !!}/>
                                    <span class="label-text">{{__('a.Allow categories?')}}</span>
                                </label>
                            </div>
                            <div class="animated-checkbox">
                                <label for="chk-{{$entry->id}}-allow_tags">
                                    <input type="checkbox" id="chk-{{$entry->id}}-allow_tags" name="allow_tags" class="form-check-input"
                                           value="{{$allowTags}}"
                                        {!! $allowTagsChecked !!}/>
                                    <span class="label-text">{{__('a.Allow tags?')}}</span>
                                </label>
                            </div>
                            <div class="animated-checkbox">
                                <label for="chk-{{$entry->id}}-allow_comments">
                                    <input type="checkbox" id="chk-{{$entry->id}}-allow_comments" name="allow_comments" class="form-check-input"
                                           value="{{$allowComments}}"
                                        {!! $allowCommentsChecked !!}/>
                                    <span class="label-text">{{__('a.Allow comments?')}}</span>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary mr-2 mt-2">{{__('a.Update')}}</button>
                            <a href="{{route('admin.settings.post_types.delete', ['id' => $entry->id])}}"
                               data-confirm="{{__('a.Are you sure you want to delete this post type? All items associated with it will also be deleted.')}}"
                               class="btn btn-danger mt-2">{{__('a.Delete')}}</a>

                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            @if($isMultiLanguage)
                <div class="col-md-6">
                    <div class="tile">
                        <h4 class="tile-title">{{__('a.Translations')}}</h4>

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

                                        $transPost = App\Models\PostType::where('language_id', $currentLanguageID)->where('translated_id', $entry->id)->first();
                                        if($transPost){
                                            $currentLanguageID = $transPost->language_id;
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
                                                <form method="post" action="{{route( 'admin.settings.post_types.translate',
                                                    [
                                                        'post_id' => $entry->id,
                                                        'language_id' => $currentLanguageID,
                                                        'new_post_id' => $transPost ? $transPost->id : 0
                                                    ])}}">
                                                    <div class="form-group">
                                                        <label for="name-field-{{$languageCode}}">Name</label>
                                                        <input type="text" class="form-control" id="name-field-{{$languageCode}}" name="name"
                                                               value="{{$transPost ? $transPost->name : ''}}"
                                                               placeholder="{{__('a.Name')}}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="display_name-field-{{$languageCode}}">{{__('a.Display name')}}</label>
                                                        <input type="text" class="form-control" id="display_name-field-{{$languageCode}}" name="display_name"
                                                               value="{{$transPost ? $transPost->display_name : ''}}"
                                                               placeholder="{{__('a.Display name')}}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="plural_name-field-{{$languageCode}}">Plural
                                                            name</label>
                                                        <input type="text" class="form-control" id="plural_name-field-{{$languageCode}}" name="plural_name"
                                                               value="{{$transPost ? $transPost->plural_name : ''}}"
                                                               placeholder="{{__('a.Plural name')}}">
                                                    </div>

                                                    <button type="submit" class="btn btn-primary mr-2">{{__('a.Update')}}</button>
                                                    @if($transPost)
                                                        <a href="{{route('admin.settings.post_types.delete', ['id' => $transPost->id])}}"
                                                           data-confirm="{{__('a.Are you sure you want to delete this post type? All items associated with it will also be deleted.')}}"
                                                           class="btn btn-danger mr-2">
                                                            {{__('a.Delete')}}
                                                        </a>
                                                    @endif

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
