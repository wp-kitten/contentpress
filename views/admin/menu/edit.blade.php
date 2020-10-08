@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit menu')}}</title>
@endsection

@section('head-scripts')
    <link href="//cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css" type="text/css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
    <style type="text/css">
        /**
         * Nestable
         */

        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 600px; list-style: none; font-size: 13px; line-height: 20px; }

        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 30px; }
        .dd-collapsed .dd-list { display: none; }

        .dd-item,
        .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

        .dd-empty{ display:none;}

        .dd-handle { display: block; height: 30px; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
            background: #fafafa;
            background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
            background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
            background:         linear-gradient(top, #fafafa 0%, #eee 100%);
            -webkit-border-radius: 3px;
            border-radius: 3px;
            box-sizing: border-box; -moz-box-sizing: border-box;
        }
        .dd-handle:hover { color: #2ea8e5; background: #fff; }

        .dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
        .dd-item > button[data-action="collapse"]:before { content: '-'; }

        .dd-placeholder,
        .dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
        .dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
            background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
        }

        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; }
        .dd-dragel .dd-handle {
            -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
            box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
        }

        /**
         * Nestable Extras
         */

        .nestable-lists { display: block; clear: both; padding: 30px 0; width: 100%; border: 0; border-top: 2px solid #ddd; border-bottom: 2px solid #ddd; }

        #nestable-menu { padding: 0; margin: 20px 0; }

        #nestable-output,
        #nestable2-output { width: 100%; height: 7em; font-size: 0.75em; line-height: 1.333333em; font-family: Consolas, monospace; padding: 5px; box-sizing: border-box; -moz-box-sizing: border-box; }

        #nestable2 .dd-handle {
            color: #fff;
            border: 1px solid #999;
            background: #bbb;
            background: -webkit-linear-gradient(top, #bbb 0%, #999 100%);
            background:    -moz-linear-gradient(top, #bbb 0%, #999 100%);
            background:         linear-gradient(top, #bbb 0%, #999 100%);
        }
        #nestable2 .dd-handle:hover { background: #bbb; }
        #nestable2 .dd-item > button:before { color: #fff; }

        /*@media only screen and (min-width: 700px) {*/

        /*    .dd { float: left; width: 48%; }*/
        /*    .dd + .dd { margin-left: 2%; }*/

        /*}*/

        .dd-hover > .dd-handle { background: #2ea8e5 !important; }

        /**
         * Nestable Draggable Handles
         */

        .dd3-content { display: block; height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
            background: #fafafa;
            background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
            background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
            background:         linear-gradient(top, #fafafa 0%, #eee 100%);
            -webkit-border-radius: 3px;
            border-radius: 3px;
            box-sizing: border-box; -moz-box-sizing: border-box;
        }
        .dd3-content:hover { color: #2ea8e5; background: #fff; }

        .dd-dragel > .dd3-item > .dd3-content { margin: 0; }

        .dd3-item > button { margin-left: 30px; }

        .dd3-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
            border: 1px solid #aaa;
            background: #ddd;
            background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
            background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
            background:         linear-gradient(top, #ddd 0%, #bbb 100%);
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .dd3-handle:before { content: '≡'; display: block; position: absolute; left: 0; top: 3px; width: 100%; text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
        .dd3-handle:hover { background: #ddd; }




        .js-btn-remove {
            position: absolute;
            right: 5px;
            top: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            color: #cc0000;
            z-index: 1000000;
        }

    </style>
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
                                        <ul id="list-1" class="mt-4 dd-list">
                                            @if($walker && $walker->hasMenuItems())
                                                {!! $walker->outputHtml($walker->getMenuItems()) !!}
                                            @endif
                                        </ul>
                                    </div>


                                    <div class="cp-form-footer-wrap mt-5">
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
                                                                    $categories = App\Models\Category::where('language_id', App\Helpers\CPML::getDefaultLanguageID())->get();
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
                                                                                ->where('language_id', App\Helpers\CPML::getDefaultLanguageID())
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
