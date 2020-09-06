@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Tags')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Tags')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_taxonomies'))
        <div class="row">
            <div class="col-md-3">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.New tag')}}</h3>

                    <form method="post" action="{{route("admin.{$__post_type->name}.tag.new")}}">

                        <div class="form-group">
                            <label for="field-name">{{__('a.Name')}}</label>
                            <input type="text" id="field-name" name="name" class="form-control" value="{{old('name')}}" placeholder="{{__('a.Name')}}"/>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">{{__('a.Add')}}</button>

                        <input type="hidden" name="language_id" value="{{$__post_type->language_id}}"/>
                        @csrf
                    </form>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tile">
                    <div class="table-responsive cp-table-align-middle">
                        <table class="table table-sm mb-5 table-hover">
                            <thead>
                            <tr>
                                <th scope="col">{{__('a.Name')}}</th>
                                <th scope="col">{{__('a.Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($tags as $tag)
                                <tr>
                                    <td>
                                        @if(App\Helpers\CPML::tagMissingTranslations($tag->id))
                                            <span class="bullet danger" title="{{__('a.This tag is missing translations.')}}"></span>
                                        @else
                                            <span class="bullet success" title="{{__('a.This tag has translations for all enabled languages.')}}"></span>
                                        @endif
                                        <span class="text-dark">{{$tag->name}}</span>
                                    </td>
                                    <td>
                                        @if(cp_is_multilingual())
                                            <a href="{{route("admin.{$__post_type->name}.tag.edit", ['id' => $tag->id])}}" class="text-primary mr-2">
                                                {{__('a.Translations')}}
                                            </a>
                                        @endif
                                        <a href="{{route("admin.{$__post_type->name}.tag.edit", ['id' => $tag->id])}}" class="text-primary">{{__('a.Edit')}}</a>
                                        <a href="{{route("admin.{$__post_type->name}.tag.delete", ['id' => $tag->id])}}"
                                           data-confirm="{{__('a.Are you sure you want to delete this tag?')}}"
                                           class="text-danger ml-2">{{__('a.Delete')}}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">{{__('a.No tags found')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        {{-- Render pagination --}}
                        {{ $tags->render() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
