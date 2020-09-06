{{-- Render the plugin info --}}
<div class="plugin-info-modal-wrap">
    <div class="thumbnail">
        @if($plugin->thumbnail)
            <img src="{{cp_plugin_url($plugin->name, $plugin->thumbnail)}}" alt="{{$plugin->display_name}}" class="img-thumbnail"/>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-condensed table-responsive">
            <tbody>
            <tr>
                <td>{{__('a.Version')}}</td>
                <td>{{$plugin->version}}</td>
            </tr>
            <tr>
                <td>{{__('a.Description')}}</td>
                <td>{{$plugin->description}}</td>
            </tr>
            <tr>
                <td>{{__('a.Page url')}}</td>
                <td>
                    <a href="{{$plugin->page_url}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">{{__('a.site')}}</a>
                </td>
            </tr>
            <tr>
                <td>{{__('a.Authors')}}</td>
                <td>
                    @foreach($plugin->authors as $author)
                        <a href="{{$author->url}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">
                            {{$author->name}}
                        </a>&nbsp;
                    @endforeach
                </td>
            </tr>
            <tr>
                <td>{{__('a.Contributors')}}</td>
                <td>
                    @forelse($plugin->contributors as $author)
                        <a href="{{$author->url}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">
                            {{$author->name}}
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
