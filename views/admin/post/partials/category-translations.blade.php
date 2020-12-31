@inject('languageClass', App\Models\Language)
<table class="table table-bordered">
    <thead>
    <tr>
        <th>{{__('a.Language')}}</th>
        <th>{{__('a.Title')}}</th>
        <th>{{__('a.Actions')}}</th>
    </tr>
    </thead>

    <tbody>
        @foreach($enabled_languages as $languageCode)
            @if($languageCode == $default_language_code)
                @continue;
            @endif

            @php
                $translation = App\Helpers\VPML::getTranslatedCategory($category->id, $languageClass->getID($languageCode))
            @endphp

            <tr>
                <td>
                    <i class="{{vp_get_flag_class($languageCode)}}"></i>
                    {{$languageClass->getNameFrom($languageCode)}}
                </td>

                <td>
                    {{$translation ? $translation->name : ''}}
                </td>

                <td>
                    @if($translation)
                        <a href="{{route( "admin.{$postType->name}.category.edit", $translation->id )}}" class="text-primary">
                            {{__( 'a.Edit' )}}
                        </a>

                        <a href="{{route( "admin.{$postType->name}.category.delete", $translation->id )}}"
                           data-confirm="{{__( 'a.Are you sure you want to delete this translation?' )}}"
                           class="text-danger">
                            {{__( 'a.Delete' )}}
                        </a>
                        @else
                        <a href="{{route("admin.{$postType->name}.category.translate", [
                            'category_id' => $category->id,
                            'language_id' => $languageClass->getID($languageCode),
                        ])}}">{{__('a.Translate')}}</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

