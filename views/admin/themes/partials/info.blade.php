{{-- Render the theme info --}}
<div class="theme-info-modal-wrap">
    <div class="thumbnail">
        @if($theme['thumbnail'])
            <img src="{{cp_theme_url($theme['name'], $theme['thumbnail'])}}" alt="{{$theme['name']}}" class="img-thumbnail"/>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-condensed table-responsive">
            <tbody>
            <tr>
                <td>{{__('a.Parent theme')}}</td>
                <td>{{$theme['extends'] ? $theme['extends'] : __('a.None')}}</td>
            </tr>
            <tr>
                <td>{{__('a.Version')}}</td>
                <td>{{$theme['version']}}</td>
            </tr>
            <tr>
                <td>{{__('a.Description')}}</td>
                <td>{{$theme['description']}}</td>
            </tr>
            <tr>
                <td>{{__('a.Page url')}}</td>
                <td>
                    <a href="{{$theme['page_url']}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">{{__('a.site')}}</a>
                </td>
            </tr>
            <tr>
                <td>{{__('a.Authors')}}</td>
                <td>
                    @foreach($theme['authors'] as $author)
                        <a href="{{$author['url']}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">
                            {{$author['name']}}
                        </a>&nbsp;
                    @endforeach
                </td>
            </tr>
            <tr>
                <td>{{__('a.Contributors')}}</td>
                <td>
                    @forelse($theme['contributors'] as $author)
                        <a href="{{$author['url']}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">
                            {{$author['name']}}
                        </a>&nbsp;
                    @empty
                        <span>{{__("a.None")}}</span>
                    @endforelse
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>
