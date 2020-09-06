@inject('catModel', App\Category)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('cpfr::m.Feeds')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('cpfr::m.Feeds')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    @if($feeds && $feeds->count())
                        <a class="btn btn-primary d-none d-inline-block" href="#"
                           onclick="event.preventDefault(); document.getElementById('cpfr-import-feeds').submit();">
                            {{__('cpfr::m.Import Feeds')}}
                        </a>
                        <form id="cpfr-import-feeds" method="post" action="{{route('admin.feeds.import')}}" class="hidden">
                            @csrf
                        </form>
                    @endif

                    <a class="btn btn-dark d-none d-inline-block" href="#"
                       onclick="event.preventDefault(); document.getElementById('cpfr-import-default-content').submit();"
                       title="{{__('cpfr::m.Creates the default categories & feed urls')}}">
                        {{__('cpfr::m.Import default content')}}
                    </a>
                    <form id="cpfr-import-default-content" method="post" action="{{route('admin.feeds.import_default_content')}}" class="hidden">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row cpfr-page-wrap">
            <div class="col-md-4">
                <div class="tile">
                    <h3 class="tile-title">{{__('cpfr::m.Add new')}}</h3>

                    <form method="post" action="{{route('admin.feeds.create', ['id' => request('id')])}}">

                        <div class="form-group">
                            <label for="feed-url-field">{{__('cpfr::m.Url')}}</label>
                            <input type="url" class="form-control" value="" name="url" id="feed-url-field"/>
                        </div>

                        <div class="form-group">
                            <label for="cat-name-field">{{__('cpfr::m.Category')}}</label>
                            <select id="cat-name-field" name="id" class="selectize-control">
                                @foreach($categories as $categoryID => $subcategories)
                                    @php
                                        $cat = $catModel->find($categoryID);
                                        if( empty( $subcategories ) ) {
                                            echo '<option value="'.esc_attr($categoryID).'">'.utf8_encode($cat->name).'</option>';
                                        }
                                        else {
                                            echo '<optgroup label="'.utf8_encode($cat->name).'">';
                                            foreach($subcategories as $subcategoryID){
                                                $subcat = $catModel->find($subcategoryID);
                                                echo '<option value="'.esc_attr($subcategoryID).'">'.utf8_encode($subcat->name).'</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    @endphp
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('cpfr::m.Add')}}</button>
                        @csrf
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('cpfr::m.All')}}</h3>

                    <div class="list-wrapper">
                        <ul class="d-flex flex-column list-unstyled list">
                            @forelse($feeds as $feed)
                                <li class="cp-flex cp-flex--center cp-flex--space-between mb-3 border-bottom">
                                    <p>
                                        <span class="d-block text-description">{!! utf8_encode($feed->category->name) !!}</span>
                                        <span class="d-block">{{$feed->url}}</span>
                                    </p>
                                    <div>
                                        <a href="{{route('admin.feeds.edit', ['id' => $feed->id])}}" class="mr-2">{{__('cpfr::m.Edit')}}</a>
                                        <a href="#"
                                           class="text-danger"
                                           data-confirm="{{__('cpfr::m.Are you sure you want to delete this feed?')}}"
                                           data-form-id="form-feed-delete-{{$feed->id}}">
                                            {{__('cpfr::m.Trash')}}
                                        </a>
                                        <form id="form-feed-delete-{{$feed->id}}" action="{{route('admin.feeds.delete', $feed->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="borderless">
                                    <div class="bs-component">
                                        <div class="alert alert-info">
                                            {{__('cpfr::m.No feeds found. Why not add one?')}}
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>

                        {{-- Render pagination --}}
                        {{ $feeds->render() }}

                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
