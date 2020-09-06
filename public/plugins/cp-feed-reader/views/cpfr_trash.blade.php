@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('cpfr::m.Trash')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('cpfr::m.Trash')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="#"
                       data-confirm="{{__('cpfr::m.Are you sure you want to empty the trash?')}}"
                       onclick="event.preventDefault(); document.getElementById('cpfr-empty-trash').submit();">
                        {{__('cpfr::m.Empty trash')}}
                    </a>
                    <form id="cpfr-empty-trash" method="post" action="{{route('admin.feeds.trash.empty')}}">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-10">
                <div class="tile">
                    <h3 class="tile-title">{{__('cpfr::m.Deleted feeds')}}</h3>

                    <div class="list-wrapper">
                        <ul class="d-flex flex-column list-unstyled list">
                            @forelse($feeds as $feed)
                                <li class="cp-flex cp-flex--center cp-flex--space-between mb-3 border-bottom">
                                    <p>
                                        <span class="d-block text-description">{!! utf8_encode($feed->category->name) !!}</span>
                                        <span class="d-block">{{$feed->url}}</span>
                                    </p>
                                    <div>
                                        <a href="#"
                                           class="mr-2"
                                           onclick="event.preventDefault(); document.getElementById('form-feed-restore-{{$feed->id}}').submit();">
                                            {{__('cpfr::m.Restore')}}
                                        </a>

                                        <a href="#"
                                           class="text-danger"
                                           data-confirm="{{__('cpfr::m.Are you sure you want to permanently delete this feed?')}}"
                                           data-form-id="form-feed-delete-{{$feed->id}}">
                                            {{__('cpfr::m.Permanently delete')}}
                                        </a>

                                        <form id="form-feed-restore-{{$feed->id}}" action="{{route('admin.feeds.trash.restore', $feed->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                        <form id="form-feed-delete-{{$feed->id}}" action="{{route('admin.feeds.trash.delete', $feed->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="borderless">
                                    <div class="bs-component">
                                        <div class="alert alert-info">
                                            {{__('cpfr::m.No feeds found in the trash')}}
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>

                        {{-- Render pagination --}}
                        {{ $feeds->render() }}

                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
