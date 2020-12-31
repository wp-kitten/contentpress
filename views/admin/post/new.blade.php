@inject('language', App\Models\Language)
@php
    $optionsClass = new App\Models\Options();

//{{-- GET THE CUSTOM OPTIONS --}}
    $allowCategories = $optionsClass->getOption("post_type_{$__post_type->name}_allow_categories", true);
    $allowTags = $optionsClass->getOption("post_type_{$__post_type->name}_allow_tags", true);
    $allowComments = $optionsClass->getOption("post_type_{$__post_type->name}_allow_comments", true);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{$__post_type->display_name}}</title>
@endsection

@section('admin-footer')

@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div class="content-section">
                <input class="form-control cp-wide" id="post_title" type="text" value="{!! $post->title !!}" placeholder="{{__('a.Post title')}}"/>
            </div>
            <ul class="list-unstyled list-inline mb-0">

                @if(vp_current_user_can('publish_posts'))
                    <li class="mr-3">
                        <a class="btn btn-primary d-block" href="{{route('admin.'.$__post_type->name.'.new')}}">{{__('a.New')}}</a>
                    </li>
                    <li class="">
                        <a href="#" class="btn btn-primary ml-4 d-block js-save-post-button" id="js-btn-post-save">{{__('a.Save')}}</a>
                    </li>
                @endif
                <li>
                    <a href="{{cp_get_post_view_url($post)}}" class="btn btn-primary ml-2 d-block view-post-button" target="_blank" title="Preview{{__('a.Preview')}}">
                        {{__('a.Preview')}}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('publish_posts'))
        <form id="post-new-form" class="post-edit-form">
            <div class="row">
                <div class="col-lg-9 d-flex align-items-stretch">
                    <div id="post-editor-container" style="width: 100%;">
                        {!! do_action('valpress/post_editor_content/before') !!}
                        {!! do_action('valpress/post_editor_content', $post->content) !!}
                        {!! do_action('valpress/post_editor_content/after') !!}
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="widget widget-post-status pt-2">
                                <div class="card-body">
                                    <h4 class="tile-title">{{__('a.Post Status')}}</h4>

                                    <div class="form-group">
                                        @if($post_statuses)
                                            <select id="post_status" class="form-control">
                                                @foreach($post_statuses as $entry)
                                                    @php $selected = ($entry->name == $default_post_status ? 'selected' : ''); @endphp
                                                    <option value="{{$entry->id}}" {!! $selected !!}>
                                                        {{$entry->display_name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($allowCategories)
                            <div class="col-12">
                                <div class="widget widget-post-categories">
                                    <div class="card-body">
                                        <h4 class="tile-title">{{__('a.Categories')}}</h4>

                                        <div class="form-group">
                                            @if($categories)
                                                <select id="post_categories" multiple size="10" class="selectize-control">
                                                    @foreach($categories as $entry)
                                                        <option value="{{$entry->id}}">{{$entry->name}}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($allowTags)
                            <div class="col-12">
                                <div class="widget widget-post-tags">
                                    <div class="card-body">
                                        <h4 class="tile-title">{{__('a.Tags')}}</h4>

                                        <div class="form-group">
                                            @if($tags)
                                                <select id="post_tags" multiple size="10" class="selectize-control">
                                                    @foreach($tags as $entry)
                                                        <option value="{{$entry->id}}">{{$entry->name}}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <div class="widget widget-post-image">
                                <div class="card-body">
                                    <h4 class="tile-title">{{__('a.Featured image')}}</h4>
                                    <div class="form-group">
                                        <div class="post_image_field-wrapper">
                                            <div class="js-image-preview">
                                                <input type="hidden" name="__post_image_id" id="__post_image_id" value=""/>
                                                <img id="__post_image_preview"
                                                     src=""
                                                     alt=""
                                                     class="thumbnail-image hidden"/>
                                                <span class="js-preview-image-delete" title="{{__('a.Remove image')}}">&times;</span>
                                            </div>
                                            <p>
                                                <button type="button"
                                                        class="btn btn-primary mt-3"
                                                        data-image-target="#__post_image_preview"
                                                        data-input-target="#__post_image_id"
                                                        data-toggle="modal"
                                                        data-target="#mediaModal">
                                                    {{__('a.Select image')}}
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="widget widget-post-excerpt">
                                <div class="card-body">
                                    <h4 class="tile-title">{{__('a.Excerpt')}}</h4>

                                    <div class="form-group">
                                        <div class="quill-scrolling-container">
                                            <div id="post_excerpt-editor">{!! $post->excerpt !!}</div>
                                        </div>
                                        <textarea id="post_excerpt" class="form-control hidden"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="widget widget-post-excerpt">
                                <div class="card-body">
                                    <h4 class="tile-title">{{__('a.Options')}}</h4>

                                    <div class="form-group">
                                        <label for="sticky_featured">{{__('a.Special state')}}</label>
                                        <select id="sticky_featured" class="form-control">
                                            <option value="0">{{__('a.Select')}}</option>
                                            <option value="sticky" @if($post->is_sticky) selected="selected" @endif>
                                                {{__('a.Sticky')}}
                                            </option>
                                            <option value="featured" @if($post->is_featured) selected="selected" @endif>
                                                {{__('a.Featured')}}
                                            </option>
                                        </select>
                                    </div>

                                    @if($allowComments)
                                        <div class="form-group">
                                            <label for="comments_enabled">{{__('a.Comments')}}</label>
                                            <select id="comments_enabled" class="form-control">
                                                <option value="-1">{{__('a.Select')}}</option>
                                                <option value="1" @if($comments_enabled) selected="selected" @endif>
                                                    {{__('a.Enabled')}}
                                                </option>
                                                <option value="0" @if(! $comments_enabled) selected="selected" @endif>
                                                    {{__('a.Disabled')}}
                                                </option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    @include('admin.partials.meta-fields', [
        'meta_fields' => $meta_fields,
        'model' => App\Models\PostMeta::class,
        'language_id' => $post->language_id,
        'fk_name' => 'post_id',
        'fk_value' => $post->id,
    ])

@endsection
