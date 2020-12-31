@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Categories')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Categories')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('manage_taxonomies'))

        <div class="row">
            <div class="col-md-3">
                <div class="tile">
                    <div class="js-sortable-containment">
                        <h4 class="tile-title">{{__('a.New category')}}</h4>

                        <form method="post"
                              class="category-form category-form-add"
                              enctype="multipart/form-data"
                              action="{{route("admin.{$__post_type->name}.category.new")}}">

                            <div class="form-group">
                                <label for="field-name">{{__('a.Name')}}</label>
                                <input type="text" id="field-name" name="name" class="form-control" value="{{old('name')}}" placeholder="{{__('a.Name')}}"/>
                            </div>

                            <div class="form-group">
                                <label for="field-description">{{__('a.Description')}}</label>
                                <textarea maxlength="255"
                                          id="field-description"
                                          name="description"
                                          placeholder="{{__('a.Description')}}"
                                          class="form-control js-text-editor hidden">{!! old('description') !!}</textarea>
                                <div class="quill-scrolling-container">
                                    <div id="field-description-editor">{!! old('description') !!}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="field-category_id">{{__('a.Parent category')}}</label>
                                <select id="field-category_id" name="category_id" class="selectize-control">
                                    <option value="0">{{__('a.Select')}}</option>
                                    @forelse($categories as $id => $name)
                                        @php $selected = ($id == session()->get('previously_selected') ? 'selected="selected"' : ''); @endphp
                                        <option value="{{$id}}" {!! $selected !!}>{!! $name !!}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="field-category_image">{{__('a.Category image')}}</label>
                                <div class="js-image-preview">
                                    <input type="hidden" name="__category_image_id" id="__category_image_id" value=""/>
                                    <img id="__category_image_preview"
                                         src=""
                                         alt=""
                                         class="thumbnail-image hidden"/>
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

                            <button type="submit" class="btn btn-primary mr-2">{{__('a.Add')}}</button>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tile">

                    <div id="categories-list-sortable">
                        @if($walker && $walker->hasCategories())

                            @php $walker->renderCategories(); @endphp

                        @else
                            <div class="alert alert-info">
                                {{__('a.No categories found')}}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(vp_is_multilingual())
            {{-- Modal --}}
            <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="infoModalLabel"></h5>
                            <button type="button" class="close text-dark" data-dismiss="modal" aria-label="{{__('a.Close')}}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="content js-content hidden"></div>
                            <div class="circle-loader js-ajax-loader hidden"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('a.Close')}}</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- END: Modal --}}
        @endif

    @endif
@endsection
