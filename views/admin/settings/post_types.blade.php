@php
    $languageClass = new App\Language();
    $optionsClass = new App\Options();
    $isMultiLanguage = (count($enabled_languages) > 1);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Post Types')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Post Types')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-4">
                <div class="tile">
                    <h4 class="tile-title">{{__('a.Add new')}}</h4>
                    <form method="post" action="{{route('admin.settings.post_types.add')}}">
                        <div class="form-group">
                            <label for="field-name">{{__('a.Name')}}</label>
                            <input type="text" name="name" id="field-name" value="{{old('name')}}" placeholder="{{__('a.Name')}}" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="field-display_name">{{__('a.Display name')}}</label>
                            <input type="text" name="display_name" id="field-display_name" value="{{old('display_name')}}" placeholder="{{__('a.Display name')}}" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="field-plural_name">{{__('a.Plural name')}}</label>
                            <input type="text" name="plural_name" id="field-plural_name" value="{{old('plural_name')}}" placeholder="{{__('a.Plural name')}}" class="form-control"/>
                        </div>

                        <div class="animated-checkbox">
                            <label for="field-allow_categories">
                                <input type="checkbox" name="allow_categories" id="field-allow_categories" value="1" class="form-check-input"/>
                                <span class="label-text">{{__('a.Allow categories?')}}</span>
                            </label>
                        </div>
                        <div class="animated-checkbox">
                            <label for="field-allow_tags">
                                <input type="checkbox" name="allow_tags" id="field-allow_tags" value="1" class="form-check-input"/>
                                <span class="label-text">{{__('a.Allow tags?')}}</span>
                            </label>
                        </div>
                        <div class="animated-checkbox">
                            <label for="field-allow_comments">
                                <input type="checkbox" name="allow_comments" id="field-allow_comments" value="1" class="form-check-input"/>
                                <span class="label-text">{{__('a.Allow comments?')}}</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2">{{__('a.Add')}}</button>

                        <input type="hidden" name="language_id" value="{{$languageClass->getID($default_language_code)}}"/>
                        @csrf
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="tile">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">{{__('a.Name')}}</th>
                                <th scope="col">{{__('a.Display name')}}</th>
                                <th scope="col">{{__('a.Plural name')}}</th>
                                <th scope="col">{{__('a.Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($post_types as $entry)
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

                                <tr id="row-{{$entry->id}}">
                                    <td class="post-type-name-cell">{{$entry->name}}</td>
                                    <td class="post-type-display-name-cell">{{$entry->display_name}}</td>
                                    <td class="post-type-plural-name-cell">{{$entry->plural_name}}</td>
                                    <td>
                                        <a href="#" class="js-button-preview text-primary mr-1" title="{{__('a.Quick edit')}}" data-id="{{$entry->id}}">
                                            {{__('a.Preview')}}
                                        </a>

                                        <a href="{{route('admin.settings.post_types.edit', [ 'id' => $entry->id])}}"
                                           class="text-primary mr-1"
                                           title="{{__('a.Edit')}}">
                                            {{__('a.Edit')}}
                                        </a>

                                        <a href="{{route('admin.settings.post_types.delete', ['id' => $entry->id])}}"
                                           data-confirm="{{__('a.Are you sure you want to delete this post? All items associated with it will also be deleted.')}}"
                                           class="text-danger"
                                           title="{{__('a.Delete')}}">
                                            {{__('a.Delete')}}
                                        </a>
                                    </td>
                                </tr>
                                {{--- PREVIEW FORM ---}}
                                <tr id="row-edit-{{$entry->id}}" class="hidden" style="background-color: #fafafa">
                                    <td colspan="4">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th scope="col">{{__('a.Name')}}</th>
                                                <th scope="col">{{__('a.Display name')}}</th>
                                                <th scope="col">{{__('a.Plural name')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="field-name form-control" value="{{$entry->name}}" placeholder="{{__('a.Name')}}" readonly/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="field-display-name form-control" value="{{$entry->display_name}}" placeholder="{{__('a.Display name')}}" readonly/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="field-plural-name form-control" value="{{$entry->plural_name}}" placeholder="{{__('a.Plural name')}}" readonly/>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="allow_categories-{{$entry->id}}">
                                                            <input type="checkbox" id="allow_categories-{{$entry->id}}" class="allow_categories form-check-input"
                                                                   disabled
                                                                   value="{{$allowCategories}}"
                                                                {!! $allowCategoriesChecked !!}/>
                                                            {{__('a.Allow categories?')}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="allow_tags-{{$entry->id}}">
                                                            <input type="checkbox" id="allow_tags-{{$entry->id}}" class="allow_tags form-check-input"
                                                                   disabled
                                                                   value="{{$allowTags}}"
                                                                {!! $allowTagsChecked !!}/>
                                                            {{__('a.Allow tags?')}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="allow_comments-{{$entry->id}}">
                                                            <input type="checkbox" id="allow_comments-{{$entry->id}}" class="allow_comments form-check-input"
                                                                   disabled
                                                                   value="{{$allowComments}}"
                                                                {!! $allowCommentsChecked !!}/>
                                                            {{__('a.Allow comments?')}}
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right">
                                                    <button type="button" class="btn btn-dark mr-2 js-button-form-close" data-id="{{$entry->id}}">{{__('a.Close')}}</button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

