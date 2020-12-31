{{-- ADMIN SIDEBAR MENU --}}
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>

@inject('language', App\Models\Language)
@inject('optionsClass', App\Models\Options)

@php
    $languages = $language->all();
    $enabledLanguages = $optionsClass->getOption( 'enabled_languages', [] );
    $isMultiLanguage = (count($enabledLanguages) > 1);

    $currentUser = cp_get_current_user();
    $userImage = cp_get_user_profile_image_url($currentUser->getAuthIdentifier());
    $postTypes = App\Models\PostType::where('language_id', App\Helpers\VPML::getDefaultLanguageID())->get();

    //#! Edit user profile text
    $profileLinkText = __('a.Your profile');
    if(request()->is('admin.users.edit.*')){
        $editedUserID = request()->route('id');
        $isOwnProfile = ( $currentUser->getAuthIdentifier() == $editedUserID );
        if(! $isOwnProfile){
            $profileLinkText = __('a.User profile');
        }
    }
@endphp

<aside class="app-sidebar">
    <div class="app-sidebar__user">
        @if($userImage)
            <img class="app-sidebar__user-avatar" src="{{$userImage}}" alt="{{__('a.Profile image')}}" style="width:48px; height:48px;"/>
        @endif
        <div>
            <p class="app-sidebar__user-name">{{ cp_get_user_display_name( $currentUser ) }}</p>
            <p class="app-sidebar__user-designation">{{$currentUser->role->name}}</p>
        </div>
    </div>
    <ul class="app-menu">
        {{-- DASHBOARD --}}
        <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.dashboard')}}">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-home"></i>
                <span class="app-menu__label">{{__('a.Dashboard')}}</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.dashboard')}}" href="{{route('admin.dashboard')}}">
                        {{__('a.Dashboard')}}
                    </a>
                </li>

                @if(cp_current_user_can(['update_plugins', 'update_themes']))
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.dashboard.updates')}}" href="{{route('admin.dashboard.updates')}}">
                            {{__('a.Updates')}}
                        </a>
                    </li>
                @endif

                @if(cp_current_user_can(['super_admin', 'administrator']))
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.dashboard.commands')}}" href="{{route('admin.dashboard.commands')}}">
                            {{__('a.Commands')}}
                        </a>
                    </li>
                @endif

                {!! do_action('valpress/admin/sidebar/menu/dashboard') !!}
            </ul>
        </li>

        {{-- POST TYPES --}}
        @if($postTypes->count() && cp_current_user_can('view_posts'))
            @foreach($postTypes as $postType)
                {{-- GET THE CUSTOM OPTIONS --}}
                @php
                    $allowCategories = $optionsClass->getOption("post_type_{$postType->name}_allow_categories", false);
                    $allowTags = $optionsClass->getOption("post_type_{$postType->name}_allow_tags", false);
                    $allowComments = $optionsClass->getOption("post_type_{$postType->name}_allow_comments", false);

                    $postTypeName = $postTypePluralName = '';

                    if(app()->getLocale() == App\Helpers\VPML::getDefaultLanguageCode()){
                        $translation = $postType;
                    }
                    else {
                        $translation = App\Helpers\VPML::postTypeGetTranslation($postType->id, app()->getLocale());
                    }

                    if(! $translation){
                        $translation = $postType;
                    }
                @endphp


                @php $routeBaseName = "admin.{$postType->name}"; @endphp
                <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem($routeBaseName)}}">
                    @php $r = "{$routeBaseName}.all"; @endphp

                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fa fa-file-text"></i>
                        <span class="app-menu__label">{{$translation->plural_name}}</span>
                        <i class="treeview-indicator fa fa-angle-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem($r)}}" href="{{route($r)}}">{{__('a.All')}}</a>
                        </li>

                        @if(cp_current_user_can('publish_posts'))
                            <li>
                                <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem($routeBaseName.'.new')}}" href="{{route($routeBaseName.'.new')}}">{{__('a.New')}} {{$translation->name}}</a>
                            </li>
                        @endif

                        @if($allowCategories && cp_current_user_can('manage_taxonomies'))
                            @php $r = "{$routeBaseName}.category.all"; @endphp
                            <li>
                                <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem($routeBaseName.'.category', true)}}" href="{{route($r)}}">{{__('a.Categories')}}</a>
                            </li>
                        @endif

                        @if($allowTags && cp_current_user_can('manage_taxonomies'))
                            @php $r = "{$routeBaseName}.tag.all"; @endphp
                            <li>
                                <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem($routeBaseName.'.tag', true)}}" href="{{route($r)}}">{{__('a.Tags')}}</a>
                            </li>
                        @endif

                        @if($allowComments && cp_current_user_can('moderate_comments'))
                            @php $r = "{$routeBaseName}.comment.all"; @endphp
                            <li>
                                <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem($routeBaseName.'.comment', true)}}" href="{{route($r)}}">{{__('a.Comments')}}</a>
                            </li>
                        @endif

                        {!! do_action('valpress/admin/sidebar/menu/posts/'.$postType->name) !!}
                    </ul>
                </li>
            @endforeach
        @endif

        {{-- MENUS --}}
        @if(cp_current_user_can('manage_menus'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.menus')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-navicon"></i>
                    <span class="app-menu__label">{{__('a.Menus')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.menus.all')}}" href="{{route('admin.menus.all')}}">{{__('a.All')}}</a>
                    </li>

                    @if(cp_current_user_can('create_menu'))
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.menus.add')}}" href="{{route('admin.menus.add')}}">{{__('a.Add new')}}</a>
                        </li>
                    @endif

                    {!! do_action('valpress/admin/sidebar/menu/menus') !!}
                </ul>
            </li>
        @endif

        {{-- MEDIA --}}
        @if(cp_current_user_can('list_media'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.media')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-photo"></i>
                    <span class="app-menu__label">{{__('a.Media')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.media.all')}}" href="{{route('admin.media.all')}}">{{__('a.All')}}</a>
                    </li>

                    @if(cp_current_user_can('upload_files'))
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.media.add')}}" href="{{route('admin.media.add')}}">{{__('a.Add new')}}</a>
                        </li>
                    @endif

                    {!! do_action('valpress/admin/sidebar/menu/media') !!}
                </ul>
            </li>
        @endif

        {{-- PLUGINS --}}
        @if(cp_current_user_can('list_plugins'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.plugins')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-puzzle-piece"></i>
                    <span class="app-menu__label">{{__('a.Plugins')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.plugins.all')}}" href="{{route('admin.plugins.all')}}">{{__('a.All')}}</a>
                    </li>

                    @if(cp_current_user_can('install_plugins'))
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.plugins.add')}}" href="{{route('admin.plugins.add')}}">{{__('a.Add new')}}</a>
                        </li>
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.plugins.marketplace')}}" href="{{route('admin.plugins.marketplace')}}">{{__('a.Marketplace')}}</a>
                        </li>
                    @endif

                    {!! do_action('valpress/admin/sidebar/menu/plugins') !!}
                </ul>
            </li>
        @endif

        {{-- THEMES --}}
        @if(cp_current_user_can('list_themes'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.themes')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-paint-brush"></i>
                    <span class="app-menu__label">{{__('a.Themes')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.themes.all')}}" href="{{route('admin.themes.all')}}">{{__('a.All')}}</a>
                    </li>

                    @if(cp_current_user_can('install_themes'))
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.themes.add')}}" href="{{route('admin.themes.add')}}">{{__('a.Add new')}}</a>
                        </li>
                        <li>
                            <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.themes.marketplace')}}" href="{{route('admin.themes.marketplace')}}">{{__('a.Marketplace')}}</a>
                        </li>
                    @endif

                    {!! do_action('valpress/admin/sidebar/menu/themes') !!}
                </ul>
            </li>
        @endif

        {{-- USERS --}}
        <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.users')}}">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-users"></i>
                <span class="app-menu__label">{{__('a.Users')}}</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                @if(cp_current_user_can('list_users'))
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.users.all')}}" href="{{route('admin.users.all')}}">{{__('a.All')}}</a>
                    </li>
                @endif

                @if(cp_current_user_can('create_users'))
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.users.add')}}" href="{{route('admin.users.add')}}">{{__('a.Add new')}}</a>
                    </li>
                @endif

                @if(cp_current_user_can('read'))
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.users.edit')}}" href="{{route('admin.users.edit', cp_get_current_user()->getAuthIdentifier())}}">
                            {{$profileLinkText}}
                        </a>
                    </li>
                @endif

                {!! do_action('valpress/admin/sidebar/menu/users') !!}
            </ul>
        </li>

        {{-- ROLES --}}
        @if(cp_current_user_can('promote_users'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.roles')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-user-tag"></i>
                    <span class="app-menu__label">{{__('a.Roles')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.roles.all')}}" href="{{route('admin.roles.all')}}">{{__('a.Roles')}}</a>
                    </li>
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.roles.capabilities')}}" href="{{route('admin.roles.capabilities')}}">{{__('a.Capabilities')}}</a>
                    </li>

                    {!! do_action('valpress/admin/sidebar/menu/roles') !!}
                </ul>
            </li>
        @endif

        {{-- SETTINGS --}}
        @if(cp_current_user_can('manage_options'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.settings')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-cogs"></i>
                    <span class="app-menu__label">{{__('a.Settings')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.settings.all')}}" href="{{route('admin.settings.all')}}">{{__('a.General settings')}}</a>
                    </li>

                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.settings.languages')}}" href="{{route('admin.settings.languages')}}">{{__('a.Languages')}}</a>
                    </li>

                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.settings.reading')}}" href="{{route('admin.settings.reading')}}">{{__('a.Reading')}}</a>
                    </li>

                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.settings.post_types')}}" href="{{route('admin.settings.post_types')}}">{{__('a.Post types')}}</a>
                    </li>

                    {!! do_action('valpress/admin/sidebar/menu/settings') !!}
                </ul>
            </li>
        @endif

        {{-- TRANSLATIONS --}}
        @if(count($enabledLanguages) > 1 && cp_current_user_can('manage_translations'))
            <li class="treeview {{App\Helpers\MenuHelper::activateMenuItem('admin.translations')}}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-language"></i>
                    <span class="app-menu__label">{{__('a.Translations')}}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.translations.core')}}"
                           href="{{route('admin.translations.core')}}">
                            {{__('a.Core')}}
                        </a>
                    </li>
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.translations.plugins')}}"
                           href="{{route('admin.translations.plugins')}}">
                            {{__('a.Plugins')}}
                        </a>
                    </li>
                    <li>
                        <a class="treeview-item {{App\Helpers\MenuHelper::activateSubmenuItem('admin.translations.themes')}}"
                           href="{{route('admin.translations.themes')}}">
                            {{__('a.Themes')}}
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        {{do_action('valpress/admin/sidebar/menu')}}
    </ul>
</aside>
