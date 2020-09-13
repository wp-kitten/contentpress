@inject('catModel', App\Category)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('np::m.Theme Options')}}</title>
@endsection

@section('main')

    <form method="post" action="{{route('admin.themes.newspaper-options.save')}}" class="np-theme-options-page-wrap">
        @csrf
        <div class="app-title">
            <div class="cp-flex cp-flex--center cp-flex--space-between">
                <div>
                    <h1>{{__('np::m.Theme Options')}}</h1>
                </div>

                @if(cp_current_user_can('manage_options'))
                    <ul class="list-unstyled list-inline mb-0">
                        <li>
                            <button type="submit" class="btn btn-primary">{{__('np::m.Save')}}</button>
                        </li>
                    </ul>
                @endif
            </div>
        </div>

        @include('admin.partials.notices')

        @if(cp_current_user_can('manage_options'))
            <div class="row">
                <div class="col-sm-12">
                    <div class="tile">
                        <h3 class="tile-title">{{__('np::m.General Options')}}</h3>

                        <div class="form-group">
                            <label for="featured-categories-field">{{__('np::m.Featured Categories')}}</label>
                            <p class="text-description">{{__('np::m.These categories will be featured on homepage. If none selected here then all main categories having at least 6 posts will be displayed')}}</p>
                            <select id="featured-categories-field" name="featured_categories[]" class="selectize-control" multiple="multiple">
                                @forelse($categories as $categoryID => $subcategories)
                                    @php
                                        $cat = $catModel->find($categoryID);
                                        if( empty( $subcategories ) ) {
                                            $selected = (in_array($categoryID, $options['featured_categories']) ? 'selected' : '');
                                            echo '<option value="'.esc_attr($categoryID).'" '.$selected.'>'.$cat->name.'</option>';
                                        }
                                        else {
                                            echo '<optgroup label="'.$cat->name.'">';
                                            foreach($subcategories as $subcategoryID){
                                            $selected = (in_array($subcategoryID, $options['featured_categories']) ? 'selected' : '');
                                                $subcat = $catModel->find($subcategoryID);
                                                echo '<option value="'.esc_attr($subcategoryID).'" '.$selected.'>'.$subcat->name.'</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    @endphp
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('np::m.Save')}}</button>
                    </div>
                </div>
            </div>
        @endif
    </form>

@endsection
