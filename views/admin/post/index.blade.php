@inject('languageClass', App\Language)
@inject('optionsClass', App\Options)
@inject('userClass', App\User)
@inject('roleClass', App\Role)
@inject('postStatusClass', App\Role)
@php
    $isMultilingual = cp_is_multilingual();
    $baseRoute = "admin.{$__post_type->name}";

    $users = $userClass->whereIn('role_id', [
        $roleClass->where('name', $roleClass::ROLE_CONTRIBUTOR)->first()->id,
        $roleClass->where('name', $roleClass::ROLE_ADMIN)->first()->id
        ])->get();
    $postStatuses = $postStatusClass->all();

    $range = range(10, 100, 10);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{$__post_type->display_name}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{$__post_type->plural_name}}
                    @if($num_posts)
                        <span>({{$num_posts}})</span>
                    @endif
                </h1>
            </div>
            @if(cp_current_user_can('publish_posts'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a class="btn btn-primary d-none d-md-block" href="{{route('admin.'.$__post_type->name.'.new')}}">{{__('a.New')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="tile mb-4">
        {{--[[ FILTERS --}}
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <form method="get" action="{{route("{$baseRoute}.all")}}" class="form-inline js-form-filters">
                        <label class="label mr-2" for="user-select-field">{{__('a.By author')}}</label>
                        <select id="user-select-field" class="form-control border ml-2" name="_user">
                            <option value="">{{__('a.All')}}</option>
                            @foreach($users as $user)
                                @if($user->posts->count())
                                    @php
                                        $selected = (request()->has('_user') && request()->get('_user') == $user->id ? 'selected="selected"' : '');
                                    @endphp
                                    <option value="{{$user->id}}" {!! $selected !!}>{{$user->display_name}}</option>
                                @endif
                            @endforeach
                        </select>

                        <label class="label ml-3 mr-2" for="post-status-select-field">{{__('a.By post status')}}</label>
                        <select id="post-status-select-field" class="form-control border ml-2" name="_status">
                            <option value="">{{__('a.All')}}</option>
                            @foreach($postStatuses as $postStatus)
                                @php
                                    $selected = (request()->has('_status') && request()->get('_status') == $postStatus->id ? 'selected="selected"' : '');
                                @endphp
                                <option value="{{$postStatus->id}}" {!! $selected !!}>{{$postStatus->display_name}}
                                </option>
                            @endforeach
                        </select>

                        <label class="label ml-3 mr-2" for="sort-select-field">{{__('a.Sort')}}</label>
                        <select id="sort-select-field" class="form-control border ml-2" name="_sort">
                            @php
                                $selected = (request()->has('_sort') && request()->get('_sort') == 'asc' ? 'selected="selected"' : '');
                            @endphp
                            <option value="asc" {!! $selected !!}>{{__('a.Asc')}}</option>
                            @php
                                $selected = (request()->has('_sort') && request()->get('_sort') == 'desc' ? 'selected="selected"' : '');
                            @endphp
                            <option value="desc" {!! $selected !!}>{{__('a.Desc')}}</option>
                        </select>

                        <label class="label ml-3 mr-2" for="paginate-select-field">{{__('a.Per page')}}</label>
                        <select id="paginate-select-field" class="form-control border ml-2" name="_paginate">
                            @foreach($range as $number)
                                @php
                                    $selected = (request()->has('_paginate') && request()->get('_paginate') == $number ? 'selected="selected"' : '');
                                @endphp
                                <option value="{{$number}}" {!! $selected !!}>{{$number}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-md btn-primary ml-5">{{__('a.Filter')}}</button>
                        <button type="submit" class="btn btn-md btn-dark ml-2 js-btn-form-filters-clear" data-url="{{route("{$baseRoute}.all")}}">{{__('a.Clear')}}</button>

                        @csrf
                    </form>
                </div>
            </div>
        </div>
        {{--[[ END FILTERS --}}

        @if(cp_current_user_can('view_posts'))
            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="posts-list">
                        @if($posts && $posts->count())
                            @php
                                //#! Check options
                                $allowCategories = $optionsClass->getOption("post_type_{$__post_type->name}_allow_categories", true);
                                $allowTags = $optionsClass->getOption("post_type_{$__post_type->name}_allow_tags", true);
                                $allowComments = $optionsClass->getOption("post_type_{$__post_type->name}_allow_comments", true);

                                $__languages = [];
                                if($isMultilingual){
                                    foreach($enabled_languages as $code){
                                        if($code == $default_language_code){
                                            continue;
                                        }
                                        $__languages[$code] = cp_get_language($code)->name;
                                    }
                                }
                            @endphp

                            <div class="table-responsive">
                                {{-- POSTS LISTING --}}
                                <table class="table table-striped table-sm mb-5 posts">
                                    <thead>
                                    <tr>
                                        <th>{{__('a.Title')}}</th>
                                        <th>{{__('a.Author')}}</th>

                                        @if($allowCategories)
                                            <th>{{__('a.Categories')}}</th>
                                        @endif

                                        @if($allowTags)
                                            <th>{{__('a.Tags')}}</th>
                                        @endif

                                        @if($allowComments)
                                            <th class="text-center">{{__('a.Comments')}}</th>
                                        @endif

                                        @if($isMultilingual)
                                            @foreach($__languages as $code => $name)
                                                <th class="text-center">
                                                    <i class="{{cp_get_flag_class($code)}}" title="{{$name}}"></i>
                                                </th>
                                            @endforeach
                                        @endif
                                        <th>{{__('a.Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($posts as $post)
                                        <tr>
                                            <td class="post-title js-post-title-cell py-1">
                                                <p>
                                                    <a class="post-title text-info js-editable"
                                                       data-id="{{$post->id}}"
                                                       data-post-type="{{$post->post_type->display_name}}"
                                                       contenteditable="true"
                                                       href="{{route("{$baseRoute}.edit", ['id' => $post->id])}}"
                                                       title="{{__('a.Edit')}}">
                                                        {{$post->title}}
                                                    </a>
                                                </p>
                                                <div class="post-actions hidden">
                                                    @if($isMultilingual)
                                                        <a href="#"
                                                           class="post-translations text-primary"
                                                           data-toggle="modal"
                                                           data-target="#modal-post-{{$post->id}}"
                                                           title="{{__('a.Show translations')}}">{{__('a.Translations')}}</a>
                                                    @endif

                                                    <a href="{{cp_get_post_view_url($post)}}"
                                                       class="post-preview text-primary"
                                                       target="_blank"
                                                       title="{{__('a.Preview')}}">
                                                        {{__('a.Preview')}}
                                                    </a>
                                                    <a class="post-edit text-primary" href="{{route('admin.'.$__post_type->name.'.edit', ['id' => $post->id])}}">{{__('a.Edit')}}</a>
                                                    <a href="{{route('admin.'.$__post_type->name.'.delete', ['id' => $post->id])}}"
                                                       data-confirm="{{__('a.Are you sure you want to delete this post? All items associated with it will also be deleted.')}}"
                                                       class="text-danger post-delete">{{__('a.Delete')}}</a>
                                                    {{do_action('contentpress/post/actions', $post->id)}}
                                                </div>
                                            </td>

                                            <td class="post-author">
                                                <p>
                                                    <a class="post-author text-primary" href="{{route('admin.users.edit', ['id' => $post->user->id])}}">{{$post->user->display_name}}</a>
                                                </p>
                                            </td>

                                            @if($allowCategories)
                                                <td class="post-categories">
                                                    <p style="word-break: break-all;">
                                                        @forelse($post->categories as $cat)
                                                            <a class="post-category text-primary" href="{{route("{$baseRoute}.category.edit", ['id' => $cat->id])}}">{{$cat->name}}</a>
                                                        @empty
                                                        @endforelse
                                                    </p>
                                                </td>
                                            @endif

                                            @if($allowTags)
                                                <td class="post-tags">
                                                    <p style="word-break: break-all;">
                                                        @forelse($post->tags as $tag)
                                                            <a class="post-tag text-primary" href="{{route("{$baseRoute}.tag.edit", ['id' => $tag->id])}}">{{$tag->name}}</a>
                                                        @empty
                                                        @endforelse
                                                    </p>
                                                </td>
                                            @endif

                                            @if($allowComments)
                                                <td class="post-comments text-center">
                                                    <p>
                                                        <a href="{{route("admin.{$__post_type->name}".'.comment.all', ['post_id' => $post->id])}}"
                                                           class="text-primary" title="{{__('a.View comments')}}">
                                                            {{$post->post_comments()->count()}}
                                                        </a>
                                                    </p>
                                                </td>
                                            @endif

                                            @if($isMultilingual)
                                                @foreach($__languages as $code => $name)
                                                    <td class="text-center">
                                                        {{-- The default language is omitted by default --}}
                                                        @if($translation = App\Helpers\CPML::getTranslatedPost($post->id, $code))
                                                            <a href="{{cp_get_post_view_url($translation)}}" title="{{__('a.Click to preview')}}" target="_blank">
                                                                <i class="fa fa-check"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{route('admin.'.$__post_type->name.'.translate', [
                                                                'id' => $post->id,
                                                                'code' => $code
                                                                ])}}">
                                                                <i class="fa fa-plus"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            @endif

                                            <td class="post-date">
                                                <p class="post-status text-primary">
                                                    {{$post->post_status->display_name}}
                                                </p>
                                                <p class="post-date text-primary">
                                                    {{date($date_format, strtotime($post->updated_at))}}
                                                </p>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                {{--# MODALS - TRANSLATIONS --}}
                                @if($isMultilingual)
                                    @forelse($posts as $post)
                                        <div id="modal-post-{{$post->id}}"
                                             class="modal fade"
                                             tabindex="-1"
                                             role="dialog"
                                             aria-labelledby="modal-post-{{$post->id}}-Label"
                                             style="display: none;"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modal-post-{{$post->id}}-Label">{{__('a.Translations')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="{{__('a.Close')}}">
                                                            <span class="mdi mdi-close" aria-hidden="true"></span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                            <tr>
                                                                <th>{{__('a.Language')}}</th>
                                                                <th>{{__('a.Title')}}</th>
                                                                <th>{{__('a.Status')}}</th>
                                                                <th>{{__('a.Actions')}}</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            @foreach($enabled_languages as $languageCode)
                                                                @if($languageCode == $default_language_code)
                                                                    @continue;
                                                                @endif

                                                                @php
                                                                    $translatedPost = App\Helpers\CPML::getTranslatedPost($post->id, $languageCode)
                                                                @endphp

                                                                <tr>
                                                                    <td class="post-language">
                                                                        <i class="{{cp_get_flag_class($languageCode)}}"></i>
                                                                        {{$languageClass->getNameFrom($languageCode)}}
                                                                    </td>
                                                                    <td class="post-title">
                                                                        @if($translatedPost && cp_current_user_can('edit_posts'))
                                                                            @php
                                                                                $url = '#';
                                                                                $title = '';

                                                                                if(cp_current_user_can('edit_posts')){
                                                                                    $url = route("admin.{$__post_type->name}.edit", ['id' => $translatedPost->id]);
                                                                                    $title = __('a.Edit');
                                                                                }
                                                                                elseif(cp_current_user_can('publish_posts')){
                                                                                    $url = cp_get_post_view_url($translatedPost);
                                                                                    $title = __('a.Preview');
                                                                                }
                                                                            @endphp
                                                                            <a href="{{$url}}" class="text-primary" title="{{$title}}">{{$translatedPost->title}}</a>
                                                                        @endif
                                                                    </td>
                                                                    <td class="post-status">
                                                                        {{$translatedPost ? $translatedPost->post_status->display_name : ''}}
                                                                    </td>

                                                                    <td class="post-actions">
                                                                        @if($translatedPost && cp_current_user_can(['administrator', 'contributor']))

                                                                            @if(cp_current_user_can('edit_posts'))
                                                                                <a class="post-edit text-primary"
                                                                                   href="{{route("admin.{$__post_type->name}.edit", ['id' => $translatedPost->id])}}">{{__('a.Edit')}}</a>
                                                                            @endif

                                                                            @if(cp_current_user_can('delete_posts'))
                                                                                <a href="{{route("admin.{$__post_type->name}.delete", ['id' => $translatedPost->id])}}"
                                                                                   data-confirm="{{__('a.Are you sure you want to delete this post? All items associated with it will also be deleted.')}}"
                                                                                   class="text-danger post-delete">{{__('a.Delete')}}</a>
                                                                            @endif

                                                                            @if(cp_current_user_can('publish_posts'))
                                                                                <a href="{{cp_get_post_view_url($translatedPost)}}" class="post-preview text-primary">
                                                                                    {{__('a.Preview')}}
                                                                                </a>
                                                                            @endif

                                                                        @elseif(cp_current_user_can('publish_posts'))
                                                                            <a class="post-translate text-primary" href="{{route('admin.'.$__post_type->name.'.translate', [
													'id' => $post->id,
													'code' => $languageCode
													])}}">
                                                                                {{__('a.Translate')}}
                                                                            </a>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-dark" data-dismiss="modal">
                                                            {{__('a.Close')}}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                @endif
                                {{--# END MODALS - TRANSLATIONS --}}

                                {{-- Render pagination --}}
                                {{ $posts->render() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                {{__('a.No posts found')}}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
