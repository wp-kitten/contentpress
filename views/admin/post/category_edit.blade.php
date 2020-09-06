@php
    $languageClass = new App\Language();
    $optionsClass = new App\Options();
    $isMultiLanguage = cp_is_multilingual();

    $categoryImageInfo = cp_get_category_image_info($category->id, $category->language_id);
    $categoryImageID = 0;
    $categoryImageUrl = '';

    if($categoryImageInfo){
        $categoryImageID = $categoryImageInfo['id'];
        $categoryImageUrl = $categoryImageInfo['url'];
    }

@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit Category')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit Category')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route("admin.{$__post_type->name}.category.all")}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_taxonomies'))
        <div class="row">
            <div class="col-md-4">
                <div class="tile">
                    @if($isMultiLanguage)
                        <h3 class="tile-title">{{__('a.Language: :language', ['language' => $category->language->name])}}</h3>
                    @endif

                    <form method="post"
                          id="form-category-edit"
                          class="category-form category-form-edit"
                          enctype="multipart/form-data"
                          action="{{route("admin.{$__post_type->name}.category.update", ['id' => $category->id])}}">

                        <div class="form-group">
                            <label for="field-name">{{__('a.Name')}}</label>
                            <input type="text" id="field-name" name="name" class="form-control border" value="{{$category->name}}" placeholder="{{__('a.Name')}}"/>
                        </div>

                        <div class="form-group">
                            <label for="field-description">{{__('a.Description')}}</label>
                            <textarea maxlength="255"
                                      id="field-description"
                                      name="description"
                                      placeholder="{{__('a.Description')}}"
                                      class="form-control js-text-editor hidden">{!! $category->description !!}</textarea>
                            <div class="quill-scrolling-container">
                                <div id="field-description-editor">{!! $category->description !!}</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-category_id">{{__('a.Parent category')}}</label>
                            <select id="field-category_id" name="category_id" class="form-control border">
                                <option value="0">{{__('a.Select')}}</option>
                                @if($categories->count())
                                    @forelse($categories as $_category)
                                        @php $selected = ($_category->id == $category->category_id ? 'selected="selected"' : ''); @endphp
                                        <option value="{{$_category->id}}" {!! $selected !!}>{{$_category->name}}
                                        </option>
                                    @empty
                                    @endforelse
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="field-category_image">{{__('a.Category image')}}</label>
                            <div class="js-image-preview">
                                <input type="hidden" name="__category_image_id" id="__category_image_id" value="{{$categoryImageID}}"/>
                                <img id="__category_image_preview"
                                     src="{{$categoryImageUrl}}"
                                     alt=""
                                     class="thumbnail-image @if(empty($categoryImageUrl)) hidden @endif"/>
                                <span class="js-preview-image-delete" title="{{__('a.Remove image')}}">&times;</span>
                            </div>
                            <p>
                                <button type="button"
                                        class="btn btn-primary mt-3"
                                        data-image-target="#__category_image_preview"
                                        data-input-target="#__category_image_id"
                                        data-toggle="modal"
                                        data-target="#mediaModal">
                                    {{__('a.Select image')}}
                                </button>
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 mr-2 js-btn-form-submit">{{__('a.Update')}}</button>
                        <a href="{{route("admin.{$__post_type->name}.category.delete", ['id' => $category->id])}}"
                           data-confirm="{{__('a.Are you sure you want to delete this category?')}}"
                           class="btn btn-danger mt-3">{{__('a.Delete')}}</a>

                        <input type="hidden" name="language_id" value="{{$category->language_id}}"/>
                        @csrf
                    </form>
                </div>
            </div>

        </div>{{-- End.row --}}

        @include('admin.partials.meta-fields', [
                'meta_fields' => $meta_fields,
                'model' => App\CategoryMeta::class,
                'language_id' => $category->language_id,
                'fk_name' => 'category_id',
                'fk_value' => $category->id,
            ])
    @endif
@endsection
