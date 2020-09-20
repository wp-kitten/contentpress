@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp
<div class="widget widget-categories bg-white p-3">
    <div class="widget-title">
        <h3 class="text-danger">{{__('np::m.Categories')}}</h3>
    </div>
    <div class="widget-content">
        <ul class="list-unstyled mt-3 mb-3 categories-list">
            @if(! empty($categories))
                @foreach($categories as $categoryID => $info)
                    <li>
                        <a class="category-name text-info" href="{{route('app.my_feeds.category', $info['category']->slug)}}">{!! $info['category']->name !!}</a>
                        <span class="num-posts text-dark">{{$info['count']}}</span>
                    </li>
                @endforeach
            @else
                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No categories found.')])
            @endif
        </ul>
    </div>
</div>
