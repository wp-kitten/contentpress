@php
    if( ! empty($excludePlugins)){
        if(isset($excludePlugins[$pluginDirName])){
            return;
        }
    }

    /**@var \App\Helpers\PluginsManager $pluginsManager*/
    $isActive = $pluginsManager->isActivePlugin($pluginDirName);
    $hasUpdate = false;
    $canDelete = false;

    if(cp_current_user_can('update_plugins')){
        $hasUpdate = false; // todo: check for updates
    }
    if(cp_current_user_can('delete_plugins')){
        $canDelete = (! $isActive );
    }
@endphp

<td class="ck-input">
    <input type="checkbox" id="js-plugin-chk" name="plugins[]" value="{{$pluginDirName}}"/>
</td>
<td class="plugin-title">
    <div>
        <a href="#"
           data-toggle="modal"
           data-target="#infoModal"
           data-name="{{$pluginDirName}}"
           data-display-name="{{$pluginInfo->display_name}}"
           class="plugin-title @if($isActive) plugin-active @else text-dark @endif">
            {{$pluginInfo->display_name}}
        </a>
    </div>
    <div class="plugin-actions mt-2">
        @if(cp_current_user_can(['activate_plugins', 'deactivate_plugins'], true))
            @if($isActive)
                <a href="{{route('admin.plugins.deactivate__get', [$pluginDirName])}}"
                   class="text-info js-plugin-deactivate">{{__('a.Deactivate')}}</a>
            @else
                <a href="{{route('admin.plugins.activate__get', [$pluginDirName])}}"
                   class="text-primary js-plugin-activate">{{__('a.Activate')}}</a>
            @endif
        @endif

        @if($hasUpdate)
            <a href="#" class="text-primary js-plugin-update">{{__('a.Update')}}</a>
        @endif

        @if($canDelete)
            <a href="{{route('admin.plugins.delete', $pluginDirName)}}"
               data-confirm="{{__('a.Are you sure you want to delete this plugin?')}}"
               class="text-danger js-plugin-delete">{{__('a.Delete')}}</a>
        @endif
    </div>
</td>
<td>{!! $pluginInfo->description !!}</td>
<td class="text-center">{{$pluginInfo->version}}</td>
<td class="text-center">
    @foreach($pluginInfo->authors as $author)
        <a href="{{$author->url}}" target="_blank" class="text-info" title="{{__('a.Opens in a new tab/window')}}">
            {{$author->name}}
        </a>
    @endforeach
</td>
