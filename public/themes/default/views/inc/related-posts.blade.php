@php
    /**@var App\Models\Post $post*/
    $postCategory = $post->categories()->first();
    if(! $postCategory){
        return;
    }
    $posts = cp_get_related_posts($post->categories()->first(), 3, $post->id);
@endphp
@if($posts && $posts->count())
    <section class="related-posts">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="section-title"><i class='fa fa-pencil-square-o'></i> {!! $title !!}</h3>
            </div>
            @foreach($posts as $post)
                <div class="col-sm-12 col-md-4">
                    <article class="loop-post mb-4">
                        <header class="article-header">
                            {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                        </header>
                        <section class="article-content">
                            <h2 class="entry-title">
                                <a href="{{cp_get_permalink($post)}}">
                                    {!! wp_kses_post($post->title) !!}
                                </a>
                            </h2>
                        </section>
                    </article>
                </div>

            @endforeach
        </div>
    </section>
@endif
