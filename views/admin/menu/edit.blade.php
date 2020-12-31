@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit menu')}}</title>
@endsection

@section('head-scripts')
    <link href="{{asset('vendor/jquery.nestable2/jquery.nestable.min.css')}}" type="text/css" rel="stylesheet"/>
    <script src="{{asset('vendor/jquery.nestable2/jquery.nestable.min.js')}}"></script>
@endsection


@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit menu')}}</h1>
            </div>

            @if(cp_current_user_can('delete_menu'))
                <ul class="list-unstyled list-inline mb-0">
                    <li>
                        <a href="#"
                           data-confirm="{{__('a.Are you sure you want to delete this menu?')}}"
                           data-form-id="form-menu-delete-{{$menu->id}}"
                           class="btn btn-danger">{{__('a.Delete')}}</a>
                        <form id="form-menu-delete-{{$menu->id}}" action="{{route('admin.menus.delete', $menu->id)}}" method="post">
                            @csrf
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_menus'))
        <div class="row">
            {{-- SIDEBAR --}}
            <div class="col-md-3">
                {{-- MENU TITLE --}}
                <div class="card">
                    <div class="card-body">
                        <h3 class="tile-title">{{__('a.Edit menu')}}</h3>
                        <form id="form-menu-name" method="post" action="{{route('admin.menus.update', ['id' => request('id')])}}">
                            <div class="form-group">
                                <label for="menu_name-field">{{__('a.Name')}}</label>
                                <input type="text" class="form-control name-field" value="{{$menu ? $menu->name : ''}}" name="menu_name" id="menu_name-field"/>
                            </div>

                            <div class="cp-form-footer-wrap">
                                <button type="submit" class="btn btn-primary js-save-menu-title-button">{{__('a.Update')}}</button>
                                <div class="circle-loader ml-2 js-ajax-loader hidden"></div>
                            </div>
                            @csrf
                        </form>
                    </div>
                </div>

                {{-- MENU OPTIONS --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <h4 class="tile-title">{{__('a.Options')}}</h4>
                        <form method="post" class="mt-3 form-menu-options" action="{{route('admin.menus.update', ['id' => request('id')])}}">

                            <div class="animated-radio-button">
                                <label>
                                    <input type="radio"
                                           @if($display_as == 'basic') checked="checked" @endif
                                           value="basic"
                                           name="display_as"
                                           id="display-as-basic"/>
                                    <span class="label-text">{{__('a.Display as basic menu')}}</span>
                                </label>
                            </div>
                            <div class="animated-radio-button">
                                <label>
                                    <input type="radio"
                                           @if($display_as == 'megamenu') checked="checked" @endif
                                           value="megamenu"
                                           name="display_as"
                                           id="display-as-megamenu"/>
                                    <span class="label-text">{{__('a.Display as mega menu')}}</span>
                                </label>
                            </div>
                            <div class="animated-radio-button">
                                <label>
                                    <input type="radio"
                                           @if($display_as == 'dropdown') checked="checked" @endif
                                           value="dropdown"
                                           name="display_as"
                                           id="display-as-dropdown"/>
                                    <span class="label-text">{{__('a.Display as dropdown menu')}}</span>
                                </label>
                            </div>

                            <div class="cp-form-footer-wrap">
                                <button type="button" class="btn btn-primary cp-submit-button js-menu-save-options-button">{{__('a.Update')}}</button>
                                <div class="circle-loader ml-2 js-ajax-loader hidden"></div>
                            </div>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-body h-100 d-flex flex-column">
                                <h4 class="tile-title">{{__('a.Build menu')}}</h4>
                                <div id="menu-items-sortable">
                                    <p class="menu-empty mt-4">
                                        {{__('a.No menu items found. Please select any items from the available content you can see on the right hand side and add them to the menu.')}}
                                    </p>

                                    <div class="dd">
                                        <ul class="mt-4 dd-list">
                                            @if($walker && $walker->hasMenuItems())
                                                {!! $walker->outputHtml($walker->getMenuItems()) !!}
                                            @endif
                                        </ul>
                                    </div>

                                    <div class="cp-form-footer-wrap mt-5">
                                        <button type="submit" class="btn btn-danger mr-2 js-btn-empty-menu">{{__('a.Empty')}}</button>
                                        <button type="submit" class="btn btn-primary hidden js-btn-save-menu">{{__('a.Save')}}</button>
                                        <div class="circle-loader js-ajax-loader hidden ml-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-body h-100 d-flex flex-column">
                                <h4 class="tile-title">{{__('a.Select menu items')}}</h4>

                                {{-- Build the tab control --}}

                                @php
                                    $menuItemTypes = App\Helpers\Util::cp_get_menu_item_types();
                                @endphp

                                @if($menuItemTypes->count() > 0)
                                    <div class="accordion cp-menu-edit-accordion" id="cp-menu-edit-accordion">
                                        {{-- Build the accordion panels --}}
                                        @foreach($menuItemTypes as $i => $type)
                                            <div class="card">
                                                <div class="card-header bg-dark" id="panel-{{$i}}" role="tab">
                                                    <div class="cp-flex cp-flex--center cp-flex--space-between">
                                                        <a data-toggle="collapse" href="#"
                                                           data-target="#collapse-{{$i}}"
                                                           aria-controls="collapse-{{$i}}"
                                                           aria-expanded="{{($i == 0) ? 'true' : 'false'}}"
                                                           class="text-light js-trigger @if($i > 0) collapsed @endif">
                                                            {{ucfirst($type->name)}}
                                                        </a>
                                                        <i class="fa @if($i == 0) fa-minus @else fa-plus @endif text-light js-sign"></i>
                                                    </div>
                                                </div>
                                                <div id="collapse-{{$i}}" class="collapse @if($i == 0) show @endif" aria-labelledby="panel-{{$i}}" data-parent="#cp-menu-edit-accordion">
                                                    <div class="card-body">
                                                        @if('custom' == $type->name)
                                                            <p class="text-muted text-small text-italic mt-3 pt-0 mb-90">
                                                                {{__('a.You can also use the custom entries to create the mega menu sections.')}}
                                                            </p>
                                                            <form>
                                                                <div class="form-group">
                                                                    <label for="menu-item-title">{{__('a.Title')}}</label>
                                                                    <input type="text" id="menu-item-title" placeholder="{{__('a.Menu item title')}}" class="form-control" required/>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="menu-item-url">{{__('a.Url')}}</label>
                                                                    <input type="text" id="menu-item-url" placeholder="{{__('a.Menu item url')}}" class="form-control" required/>
                                                                    <span class="text-muted text-small text-italic">{{__('a.You can use a route name instead of the full URL.')}}</span>
                                                                </div>

                                                                <input type="hidden" id="menu-item-data-type" value="{{$type->name}}"/>
                                                                <input type="button"
                                                                       class="js-custom-add-to-menu-button btn btn-primary mb-3"
                                                                       value="{{__('a.Add to menu')}}"/>
                                                            </form>
                                                        @elseif('category' == $type->name)
                                                            <ul class="list-unstyled mt-2 category-list js-scrollable-list custom-scroll">
                                                                @php
                                                                    $categories = App\Models\Category::where('language_id', App\Helpers\VPML::getDefaultLanguageID())->get();
                                                                @endphp
                                                                @foreach($categories as $category)
                                                                    <li class="list-item">
                                                                        <div class="cp-flex cp-flex--center">
                                                                            <input id="chk-{{$type->name}}-{{$category->id}}"
                                                                                   type="checkbox"
                                                                                   class="js-check-input"
                                                                                   data-type="{{$type->name}}"
                                                                                   data-title="{{$category->name}}"
                                                                                   data-menu-item-id="0"
                                                                                   value="{{$category->id}}"/>
                                                                            <label class="cp-block3 form-check-label ml-2" for="chk-{{$type->name}}-{{$category->id}}">
                                                                                {{$category->name}}
                                                                            </label>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            <input type="button"
                                                                   class="js-cat-add-to-menu-button js-btn-add-to-menu btn btn-primary mb-3"
                                                                   data-target=".category-list"
                                                                   value="{{__('a.Add to menu')}}"/>
                                                        @else
                                                            <ul class="list-unstyled mt-2 posts-list js-scrollable-list custom-scroll">
                                                                @php
                                                                    $entries = App\Models\Post::where('post_type_id', App\Models\PostType::where('name', $type->name)->first()->id)
                                                                                ->where('language_id', App\Helpers\VPML::getDefaultLanguageID())
                                                                                ->get();
                                                                @endphp
                                                                @foreach($entries as $post)
                                                                    <li class="list-item">
                                                                        <div class="cp-flex cp-flex--center">
                                                                            <input id="chk-{{$type->name}}-{{$post->id}}"
                                                                                   type="checkbox"
                                                                                   class="js-check-input"
                                                                                   data-type="{{$type->name}}"
                                                                                   data-title="{{$post->title}}"
                                                                                   data-menu-item-id="0"
                                                                                   value="{{$post->id}}"/>
                                                                            <label class="cp-block3 form-check-label ml-2" for="chk-{{$type->name}}-{{$post->id}}">
                                                                                {{$post->title}}
                                                                            </label>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            <input type="button"
                                                                   class="js-posts-add-to-menu-button js-btn-add-to-menu btn btn-primary mb-3"
                                                                   data-target=".posts-list"
                                                                   value="{{__('a.Add to menu')}}"/>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        {{-- End accordion panels --}}
                                    </div>
                                @endif

                            </div>

                        </div>
                    </div>
                    {{-- END .row --}}

                </div>
            </div>
        </div>
    @endif
@endsection
