@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@inject('postStatus', App\Models\PostStatus)
@inject('catModel', App\Models\Category)
@extends('layouts.frontend')

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\PostStatus $postStatus */
    /**@var App\Models\Category $catModel */

    $postStatusID = $postStatus->where('name', 'publish')->first()->id;

    //#! Get homepage sections
    $sections = $newspaperHelper->getThemeOption('homepage', []);

    //#! Cache
    $cache = app('cp.cache');
@endphp



@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@section('sidenav')
    <aside class="site-sidebar">
        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection

@section('content')
    <main class="site-page page-home">

        <div class="container">

            <div class="row">
                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">
                    @if(! empty($sections))
                        @foreach($sections as $sectionName => $catID)
                            @php
                                $sectionID = str_replace('section-', '', $sectionName);
                                $category = $catModel->find($catID);
                            @endphp
                            @include('partials.homepage.section-'.$sectionID, [
                                'category' => $category,
                                'newspaperHelper' => $newspaperHelper,
                                'postStatusID' => $postStatusID,
                                'cache' => $cache,
                            ])
                        @endforeach
                    @endif
                </div>

                {{-- SIDEBAR --}}
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar mt-3">
                        @include('components.home-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                </div>
            </div>

        </div>

    </main>
@endsection
