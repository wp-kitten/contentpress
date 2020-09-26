@php
    /**@var App\Models\Post $post*/
    $postCategory = $post->categories()->first();
    if(! $postCategory){
        return;
    }
    $_posts = cp_get_related_posts($post->categories()->first(), 12, $post->id);
@endphp
@if($_posts && $_posts->count())
    <section class="related-posts">
        <h3 class="section-title"><i class='fa fa-pencil-square-o'></i> {!! $title !!}</h3>
        <section class="slider-nav text-right">
            <a class="btn-prev" href="#" title="{{__('np::m.Previous')}}"><i class="fas fa-chevron-left nav-icon"></i></a>
            <a class="btn-next" href="#" title="{{__('np::m.Next')}}"><i class="fas fa-chevron-right nav-icon"></i></a>
        </section>
        <div class="siema-slider siema slider-wrap mt-3">
            @forelse($_posts as $entry)
                <div class="slide-item">
                    <article class="hentry-loop">
                        <div class="hentry-header">
                            {!! $newspaperHelper->getPostImageOrPlaceholder($entry, '', 'image-responsive', ['alt' => $entry->title]) !!}
                        </div>
                        <div class="hentry-content">
                            <h4 class="hentry-title"><a href="{{cp_get_permalink($entry)}}">{!! $entry->title !!}</a>
                            </h4>
                        </div>
                    </article>
                </div>
            @empty
            @endforelse
        </div>
    </section>
@endif
