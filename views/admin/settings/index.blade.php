@inject('languageClass', App\Models\Language)

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Settings')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.General settings')}}</h1>
            </div>
            @if(cp_current_user_can('manage_options'))
                <ul class="list-unstyled list-inline mb-0">
                    @if($use_internal_cache)
                        <li class="">
                            <a href="{{route('admin.cache.clear')}}" class="btn btn-primary">{{__('a.Clear cache')}}</a>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <form class="" method="post" action="{{route('admin.settings.general.update')}}">
            <div class="row">
                <div class="col-md-6">
                    <div class="tile">
                        @if(cp_is_multilingual())
                            <div class="form-group">
                                <label for="default_language">{{__('a.Select the default language')}}</label>
                                <select id="default_language" name="default_language" class="ml-2 form-control cp-form-control-inline">
                                    @foreach($enabled_languages as $langCode)
                                        @php
                                            $selected = ( ($langCode == $default_language_code) ? 'selected="selected"' : '');
                                        @endphp
                                        <option value="{{$langCode}}" {!! $selected !!}>
                                            {{$languageClass->getNameFrom($langCode)}}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-description">
                                    {{__('a.You should only change the default language if you don\'t have any translations, otherwise it will break your website!')}}
                                </p>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="default_post_status">{{__('a.Select the default post status')}}</label>
                            <select id="default_post_status" name="default_post_status" class="ml-2 form-control cp-form-control-inline">
                                @foreach($post_statuses as $entry)
                                    @php
                                        $selected = ( ($entry->name == $default_post_status) ? 'selected="selected"' : '');
                                    @endphp
                                    <option value="{{$entry->name}}" {!! $selected !!}>
                                        {{$entry->display_name}}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-description">{{__('a.Applies to all custom post types.')}}</p>
                        </div>

                        <div class="form-group">
                            <label for="default_comment_status">{{__('a.Select the default comment status')}}</label>
                            <select id="default_comment_status" name="default_comment_status" class="ml-2 form-control cp-form-control-inline">
                                @foreach($comment_statuses as $entry)
                                    @php
                                        $selected = ( ($entry->name == $default_comment_status) ? 'selected="selected"' : '');
                                    @endphp
                                    <option value="{{$entry->name}}" {!! $selected !!}>
                                        {{$entry->display_name}}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-description">{{__('a.Applies to all custom post types that have comments enabled.')}}</p>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="user_registration_open">
                                    <input type="checkbox"
                                           id="user_registration_open"
                                           name="user_registration_open"
                                           value="1"
                                           @if($user_registration_open) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text">{{__('a.Anyone can register')}}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="registration_verify_email">
                                    <input type="checkbox"
                                           id="registration_verify_email"
                                           name="registration_verify_email"
                                           value="1"
                                           @if($registration_verify_email) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text">{{__('a.Require email validation after registration')}}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="allow_user_reset_password">
                                    <input type="checkbox"
                                           id="allow_user_reset_password"
                                           name="allow_user_reset_password"
                                           value="1"
                                           @if($allow_user_reset_password) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text">{{__('a.Allow users to reset their passwords?')}}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="anyone_can_comment">
                                    <input type="checkbox"
                                           id="anyone_can_comment"
                                           name="anyone_can_comment"
                                           value="1"
                                           @if($anyone_can_comment) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text"> {{__('a.Anyone can comment')}}</span>
                                </label>
                            </div>
                            <p class="text-description">{{__('a.If not checked, only registered users can comment')}}</p>
                        </div>

                        <div class="form-group">
                            <label for="use_internal_cache" class="form-check-label">
                                <div class="animated-checkbox">
                                    <label for="use_internal_cache">
                                        <input type="checkbox"
                                               id="use_internal_cache"
                                               name="use_internal_cache"
                                               value="1"
                                               @if($use_internal_cache) checked="checked" @endif
                                               class=""/>
                                        <span class="label-text">{{__('a.Use the internal Cache system')}}</span>
                                    </label>
                                </div>
                            </label>
                            <p class="text-description">{{__('a.If selected, the ValPress Caching System will be used.')}}</p>
                        </div>

                        <div class="form-group @if($is_under_maintenance) mb-0 @endif">
                            <div class="animated-checkbox">
                                <label for="is_under_maintenance">
                                    <input type="checkbox"
                                           id="is_under_maintenance"
                                           name="is_under_maintenance"
                                           value="1"
                                           @if($is_under_maintenance) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text">{{__('a.Enable Maintenance Mode?')}}</span>
                                </label>
                            </div>
                            <p class="text-description">{{__('a.Administrator users will still be allowed to access and interact with the website.')}}</p>
                        </div>
                        @if($is_under_maintenance)
                            <div class="bg-light p-3 mb-4">
                                <div class="form-group">
                                    <label for="site_title">{{__('a.Title')}}</label>
                                    <input type="text" name="under_maintenance_page_title" class="form-control"
                                           value="{{isset($under_maintenance_page_title) ? $under_maintenance_page_title : ''}}"/>
                                </div>
                                <div class="form-group">
                                    <label for="site_title">{{__('a.Message')}}</label>
                                    <textarea name="under_maintenance_message" rows="5" class="form-control">{{isset($under_maintenance_message) ? $under_maintenance_message : ''}}</textarea>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="default_user_role">{{__('a.Select the default user role')}}</label>
                            <select id="default_user_role" name="default_user_role" class="ml-2 form-control cp-form-control-inline">
                                @foreach($roles as $entry)
                                    @php
                                        $selected = ( ($entry->id == $default_user_role) ? 'selected="selected"' : '');
                                    @endphp
                                    <option value="{{$entry->id}}" {!! $selected !!}>
                                        {{ucfirst($entry->name)}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2">
                            {{__('a.Save')}}
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="tile">
                        <div class="form-group">
                            <label for="site_title">{{__('a.Site title')}}</label>
                            <input type="text" id="site_title" name="site_title" class="form-control" value="{{$site_title}}"/>
                        </div>

                        <div class="form-group">
                            <label for="site_description">{{__('a.Site description')}}</label>
                            <input type="text" id="site_description" name="site_description" class="form-control" value="{{$site_description}}"/>
                        </div>

                        <div class="form-group">
                            <label for="blog_title">{{__('a.Blog title')}}</label>
                            <input type="text" id="blog_title" name="blog_title" class="form-control" value="{{$blog_title}}"/>
                        </div>

                        <div class="form-group">
                            <label for="date_format">{{__('a.Date format')}}</label>
                            <input type="text" id="date_format" name="date_format" class="form-control" value="{{$date_format}}"/>
                            <p class="text-description">{{__('Ex:')}} {{date($date_format)}}</p>
                        </div>

                        <div class="form-group">
                            <label for="time_format">{{__('a.Time format')}}</label>
                            <input type="text" id="time_format" name="time_format" class="form-control" value="{{$time_format}}"/>
                            <p class="text-description">{{__('Ex:')}} {{date($time_format)}}</p>
                        </div>

                        {!! do_action('valpress/admin/after-general-settings', $settings) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                {!! do_action('valpress/admin/custom-settings/render', $settings) !!}
            </div>
            @csrf
        </form>
    @endif

@endsection
