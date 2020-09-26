@php
    /**@var App\Helpers\Cache $cacheClass*/
    $cacheClass = app('cp.cache');
    $cacheKey = 'top-bar-latest-news';
    $latestNews = $cacheClass->get($cacheKey, '');
    if( empty( $latestNews ) )
    {
        $latestNews = App\Models\Post::where( 'language_id', cp_get_frontend_user_language_id() )
                    ->where( 'post_status_id', App\Models\PostStatus::where( 'name', 'publish' )->first()->id )
                    ->where( 'post_type_id', App\Models\PostType::where('name', 'post')->first()->id )
                    //#! Only include results from within the last month
                    ->whereDate( 'created_at', '>', Carbon\Carbon::now()->subMonth() )
                    ->inRandomOrder()
                    ->limit(20)
                    ->get();
        $cacheClass->set( $cacheKey, $latestNews );
    }
@endphp

<div class="news-ticker-wrap">
    <div class="jctkr-label news-ticker-label">
        {{__('np::m.Latest News')}}
    </div>
    <ul>
        @if($latestNews && $latestNews->count())
            @foreach($latestNews as $entry)
                <li>
                    <a href="{{cp_get_permalink($entry)}}">
                        {!! wp_kses_post($entry->title) !!}
                    </a>
                </li>
            @endforeach
        @endif
    </ul>
</div>

