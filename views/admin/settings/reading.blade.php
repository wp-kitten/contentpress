@php
    $languageClass = new App\Models\Language();
    $optionsClass = new App\Models\Options();

@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Reading Settings')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Reading Settings')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-6">
                <div class="tile">
                    <form class="" method="post" action="{{route('admin.settings.reading.update')}}">

                        <div class="form-group">
                            <label for="posts_per_page">
                                {{__('a.Specify the number of posts per page')}}
                                <input type="number"
                                       min="1"
                                       id="posts_per_page"
                                       name="posts_per_page"
                                       class="ml-2 form-control cp-form-control-inline"
                                       value="{{$posts_per_page}}"/>
                            </label>
                            <p class="card-description">{{__('a.Applies to all custom post types.')}}</p>
                        </div>

                        <div class="form-group">
                            <label for="comments_per_page">
                                {{__('a.Specify the number of comments per page')}}
                                <input type="number"
                                       min="1"
                                       id="comments_per_page"
                                       name="comments_per_page"
                                       class="ml-2 form-control cp-form-control-inline"
                                       value="{{$posts_per_page}}"/>
                            </label>
                            <p class="card-description">{{__('a.Applies to all custom post types.')}}</p>
                        </div>

                        <div class="form-group">
                            <label for="">{{__('a.Show on front')}}</label>
                            <select id="show_on_front" name="show_on_front" class="ml-2 form-control cp-form-control-inline">
                                <option value="blog" @if($show_on_front == 'blog') selected="selected" @endif>
                                    {{__('a.Blog page')}}
                                </option>
                                <option value="page" @if($show_on_front == 'page') selected="selected" @endif>
                                    {{__('a.Page')}}
                                </option>
                            </select>
                        </div>
                        <div id="js-page_on_front" class="form-group @if($show_on_front == 'blog') hidden @endif">
                            <label for="page_on_front">{{__('a.Page')}}</label>
                            <select id="page_on_front" name="page_on_front" class="ml-2 form-control cp-form-control-inline">
                                @foreach($pages as $page)
                                    @php
                                    $selectedPageOnFront = $page_on_front;
                                    $selected = ($selectedPageOnFront && $selectedPageOnFront == $page->id ? 'selected' : '');
                                    @endphp
                                    <option value="{{$page->id}}" {!! $selected !!}>{{$page->title}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="blog_page">{{__('a.Blog page')}}</label>
                            <select id="blog_page" name="blog_page" class="ml-2 form-control cp-form-control-inline">
                                <option value="0">{{__('a.Select')}}</option>
                                @foreach($pages as $page)
                                    @php
                                        $selectedPage = $blog_page;
                                        $selected = ($selectedPage && $selectedPage == $page->id ? 'selected' : '');
                                    @endphp
                                    <option value="{{$page->id}}" {!! $selected !!}>{{$page->title}}</option>
                                @endforeach
                            </select>
                        </div>


                        <script>
                            // Show/hide the pages selector depending on the selected value of the show_on_front option
                            jQuery( function ($) {
                                "use strict";

                                $( '#show_on_front' ).on( 'change', function (ev) {
                                    ev.preventDefault();
                                    var target = $( '#js-page_on_front' );
                                    if ( $( this ).val() === 'page' ) {
                                        target.removeClass( 'hidden' );
                                    }
                                    else {
                                        target.addClass( 'hidden' );
                                    }
                                } );
                            } )
                        </script>

                        <button type="submit" class="btn btn-primary mr-2">
                            {{__('a.Save')}}
                        </button>

                        @csrf
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
