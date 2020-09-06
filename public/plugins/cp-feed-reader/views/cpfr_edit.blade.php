@inject('catModel', App\Category)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('cpfr::m.Edit feed')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('cpfr::m.Edit feed')}}</h1>
            </div>
            @if(cp_current_user_can('manage_options'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.feeds.all')}}" class="btn btn-primary">{{__('cpfr::m.Back')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row cpfr-page-wrap">
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('cpfr::m.Edit')}}</h3>

                    <form method="post" action="{{route('admin.feeds.update', $feed->id)}}">
                        <div class="form-group">
                            <label for="feed-url-field">{{__('cpfr::m.Url')}}</label>
                            <input type="url" class="form-control" value="{{$feed->url}}" name="url" id="feed-url-field"/>
                        </div>

                        <div class="form-group">
                            <label for="cat-name-field">{{__('cpfr::m.Category')}}</label>
                            <select id="cat-name-field" name="id" class="selectize-control">
                                @foreach($categories as $categoryID => $subcategories)
                                    @php
                                        $cat = $catModel->find($categoryID);
                                        if( empty( $subcategories ) ) {
                                            $selected = ($categoryID == $feed->category->id ? 'selected' : '');
                                            echo '<option value="'.esc_attr($categoryID).'" '.$selected.'>'.utf8_encode($cat->name).'</option>';
                                        }
                                        else {
                                            echo '<optgroup label="'.utf8_encode($cat->name).'">';
                                            foreach($subcategories as $subcategoryID){
                                                $selected = ($subcategoryID == $feed->category->id ? 'selected' : '');
                                                $subcat = $catModel->find($subcategoryID);
                                                echo '<option value="'.esc_attr($subcategoryID).'" '.$selected.'>'.utf8_encode($subcat->name).'</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    @endphp
                                @endforeach
                            </select>
                        </div>


                        <button type="submit" class="btn btn-primary">{{__('cpfr::m.Update')}}</button>
                        @csrf
                    </form>
                </div>
            </div>

        </div>
    @endif
@endsection
