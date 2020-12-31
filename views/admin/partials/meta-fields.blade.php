{{--
Required param: $meta_fields
Required param: $model. Ex: App\Models\UserMeta::class
Required param: $language_id
Required param: $fk_name
Required param: $fk_value
--}}
@php
    use App\Helpers\MetaFields;
@endphp

<script id="custom-fields">
    jQuery( function ($) {
        "use strict";

        //#! Add
        var ValPressCustomFields = {
            locale: window.AppLocale,
            ajax_actions: {
                add: 'add_custom_field',
                update: 'update_custom_field',
                delete: 'delete_custom_field',
            },
            add: {
                cf_nameField: $( '#cf_name' ),
                cf_valueField: $( '#cf_value' ),
                btnSubmit: $( '#js-button-cf-fields-add' ),
                fk_nameField: $( '#js-fk_name' ),
                fk_valueField: $( '#js-fk_value' ),
                fk_modelField: $( '#js-model' ),
                fk_languageIdField: $( '#js-language_id' ),
                clearFields: function () {
                    this.cf_nameField.val( '' );
                    this.cf_valueField.val( '' );
                },
            },
            list: {
                cf_list: $( '#js-cf-list' ),
            },
            row_template: function (cfID, cfName, cfValue) {
                //#! Placeholders
                //============================
                // __CF_ID__
                // __CF_NAME__
                // __CF_VALUE__
                //============================
                var str = '<tr id="row-__CF_ID__">\n' +
                    '\t\t<td>\n' +
                    '\t\t\t<div class="form-group">\n' +
                    '\t\t\t\t<label for="cf_name___CF_ID__">Name</label>\n' +
                    '\t\t\t\t<input type="text"\n' +
                    '\t\t\t\t\t   class="form-control js-dynamic-cf-name-field"\n' +
                    '\t\t\t\t\t   id="cf_name___CF_ID__"\n' +
                    '\t\t\t\t\t   name="cf_name"\n' +
                    '\t\t\t\t\t   value="__CF_NAME__"\n' +
                    '\t\t\t\t\t   autocomplete="off"\n' +
                    '\t\t\t\t\t   placeholder="Name" required/>\n' +
                    '\t\t\t</div>\n' +
                    '\t\t</td>\n' +
                    '\t\t<td>\n' +
                    '\t\t\t<div class="form-group">\n' +
                    '\t\t\t\t<label for="cf_value___CF_ID__">Value</label>\n' +
                    '\t\t\t\t<input type="text" class="form-control js-dynamic-cf-value-field"\n' +
                    '\t\t\t\t\t   id="cf_value___CF_ID__"\n' +
                    '\t\t\t\t\t   name="cf_value"\n' +
                    '\t\t\t\t\t   value="__CF_VALUE__"\n' +
                    '\t\t\t\t\t   autocomplete="off"\n' +
                    '\t\t\t\t\t   placeholder="Value" required/>\n' +
                    '\t\t\t</div>\n' +
                    '\t\t</td>\n' +
                    '\t\t<td style="vertical-align: middle; padding-top: 0px;">\n' +
                    '\t\t\t<a href="#"\n' +
                    '\t\t\t   data-id="__CF_ID__"\n' +
                    '\t\t\t   title="Update"\n' +
                    '\t\t\t   class="btn btn-primary btn-sm mr-2 mt-4 text-center js-link-update-cf">\n' +
                    '\t\t\t\t<i class="fa fa-save mr-0"></i>\n' +
                    '\t\t\t</a>\n' +
                    '\t\t\t<a href="#"\n' +
                    '\t\t\t   data-id="__CF_ID__"\n' +
                    '\t\t\t   class="btn btn-danger btn-sm mr-2 mt-4 js-link-delete-cf"\n' +
                    '\t\t\t   title="Delete">\n' +
                    '\t\t\t\t<i class="fa fa-times mr-0"></i>\n' +
                    '\t\t\t</a>\n' +
                    '\n' +
                    '\t\t</td>\n' +
                    '</tr>';

                //#! Replace placeholders
                str = str.replace( /__CF_ID__/g, cfID )
                    .replace( /__CF_NAME__/g, cfName )
                    .replace( /__CF_VALUE__/g, cfValue );

                return str;
            },

            __hideEmptyRow: function () {
                $( '#thead-custom-fields-table' ).removeClass( 'hidden' );
                $( '#js-empty-row' ).addClass( 'hidden' );
            },
            __showEmptyRow: function () {
                $( '#thead-custom-fields-table' ).addClass( 'hidden' );
                $( '#js-empty-row' ).removeClass( 'hidden' );
            },
            __hasEntries: function () {
                return ( $( '.js-table-row', this.list.cf_list ).length > 0 );
            },

            init: function () {
                var $this = this;

                this.add.btnSubmit.on( 'click', function (ev) {
                    ev.preventDefault();
                    var self = $( this ),
                        ajaxConfig = {
                            url: $this.locale.ajax.url,
                            method: 'POST',
                            cache: false,
                            async: true,
                            timeout: 29000,
                            data: {
                                action: $this.ajax_actions.add,
                                cf_name: $this.add.cf_nameField.val(),
                                cf_value: $this.add.cf_valueField.val(),
                                fk_name: $this.add.fk_nameField.val(),
                                fk_value: $this.add.fk_valueField.val(),
                                model: $this.add.fk_modelField.val(),
                                language: $this.add.fk_languageIdField.val(),
                                [$this.locale.nonce_name]: $this.locale.nonce_value,
                            }
                        };

                    self.addClass( 'no-click' );

                    $.ajax( ajaxConfig )
                        .done( function (response) {
                            if ( response ) {
                                if ( response.success ) {
                                    $this.__hideEmptyRow();

                                    // Prepend fields
                                    $this.list.cf_list.prepend( $this.row_template(
                                        response.data.id,
                                        response.data.name,
                                        $this.add.cf_valueField.val(),
                                        $this.add.fk_modelField.val(),
                                        $this.add.fk_languageIdField.val()
                                    ) );
                                    showToast( "{{__('a.Custom field added.')}}", 'success' );
                                    $this.add.clearFields();
                                    $this.__setupDynamicListeners( $( '#row-' + response.data.id ) );
                                }
                                else {
                                    if ( response.data ) {
                                        showToast( response.data );
                                    }
                                    else {
                                        showToast( $this.locale.ajax.empty_response );
                                    }
                                }
                            }
                            else {
                                showToast( $this.locale.ajax.no_response );
                            }
                        } )
                        .fail( function (x, s, e) {
                            showToast( e, 'error' );
                        } )
                        .always( function () {
                            self.removeClass( 'no-click' );
                        } );
                } );

                this.__setupDynamicListeners( this.list.cf_list );
            },

            __setupDynamicListeners: function ($context) {
                var $this = this;

                $( '.js-link-update-cf', $context ).on( 'click', function (ev) {
                    ev.preventDefault();
                    var self = $( this ),
                        cfID = self.attr( 'data-id' ),
                        ajaxConfig = {
                            url: $this.locale.ajax.url,
                            method: 'POST',
                            cache: false,
                            async: true,
                            timeout: 29000,
                            data: {
                                action: $this.ajax_actions.update,
                                cf_id: cfID,
                                cf_name: $( '#cf_name_' + cfID ).val(),
                                cf_value: $( '#cf_value_' + cfID ).val(),
                                model: $this.add.fk_modelField.val(),
                                language: $this.add.fk_languageIdField.val(),
                                [$this.locale.nonce_name]: $this.locale.nonce_value,
                            }
                        };

                    self.addClass( 'no-click' );

                    $.ajax( ajaxConfig )
                        .done( function (response) {
                            if ( response ) {
                                if ( response.success ) {
                                    showToast( response.data, 'success' );
                                }
                                else {
                                    if ( response.data ) {
                                        showToast( response.data );
                                    }
                                    else {
                                        showToast( $this.locale.ajax.empty_response );
                                    }
                                }
                            }
                            else {
                                showToast( $this.locale.ajax.no_response );
                            }
                        } )
                        .fail( function (x, s, e) {
                            showToast( e, 'error' );
                        } )
                        .always( function () {
                            self.removeClass( 'no-click' );
                        } );
                } );

                $( '.js-link-delete-cf', $context ).on( 'click', function (ev) {
                    ev.preventDefault();
                    var self = $( this );

                    self.addClass( 'no-click' );

                    if ( !confirm( "{{__('a.Are you sure you want to delete this custom field?')}}" ) ) {
                        self.removeClass( 'no-click' );
                        return false;
                    }

                    var cfID = self.attr( 'data-id' ),
                        ajaxConfig = {
                            url: $this.locale.ajax.url,
                            method: 'POST',
                            cache: false,
                            async: true,
                            timeout: 29000,
                            data: {
                                action: $this.ajax_actions.delete,
                                cf_id: cfID,
                                model: $this.add.fk_modelField.val(),
                                [$this.locale.nonce_name]: $this.locale.nonce_value,
                            }
                        };

                    $.ajax( ajaxConfig )
                        .done( function (response) {
                            if ( response ) {
                                if ( response.success ) {
                                    showToast( response.data, 'success' );

                                    $( '#row-' + cfID ).remove();

                                    if ( !$this.__hasEntries() ) {
                                        $this.__showEmptyRow();
                                    }
                                }
                                else {
                                    if ( response.data ) {
                                        showToast( response.data );
                                    }
                                    else {
                                        showToast( $this.locale.ajax.empty_response );
                                    }
                                }
                            }
                            else {
                                showToast( $this.locale.ajax.no_response );
                            }
                        } )
                        .fail( function (x, s, e) {
                            showToast( e, 'error' );
                        } )
                        .always( function () {
                            self.removeClass( 'no-click' );
                        } );
                } );
            },
        };

        ValPressCustomFields.init();
    } );
</script>

{{-- CUSTOM FIELDS --}}
@if(cp_current_user_can('manage_custom_fields'))
    <div class="row">
        <div class="col-12">
            <div class="row flex-grow">
                <div class="col-4">
                    <div class="tile">
                        <div class="card-body">
                            <h4 class="tile-title">{{__('a.New custom field')}}</h4>

                            <form id="cf-form">
                                <div class="form-group">
                                    <label for="cf_name">{{__('a.Name')}}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="cf_name"
                                           name="cf_name"
                                           value=""
                                           autocomplete="off"
                                           placeholder="{{__('a.Name')}}" required/>
                                </div>
                                <div class="form-group">
                                    <label for="cf_value">{{__('a.Value')}}</label>
                                    <input type="text" class="form-control"
                                           id="cf_value"
                                           name="cf_value"
                                           value=""
                                           autocomplete="off"
                                           placeholder="{{__('a.Value')}}" required/>
                                </div>

                                <button type="button" id="js-button-cf-fields-add" class="btn btn-primary mr-2">{{__('a.Add')}}</button>

                                {{-- Required request params --}}
                                <input type="hidden" name="fk_name" id="js-fk_name" value="{{$fk_name}}"/>
                                <input type="hidden" name="fk_value" id="js-fk_value" value="{{$fk_value}}"/>
                                <input type="hidden" name="model" id="js-model" value="{{$model}}"/>
                                <input type="hidden" name="language_id" id="js-language_id" value="{{$language_id}}"/>
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <div class="tile">
                        <div class="card-body">
                            <h4 class="tile-title">{{__('a.Custom fields')}}</h4>

                            <div class="table-responsive">
                                <table class="table">
                                    <tbody id="js-cf-list">
                                    @forelse($meta_fields as $cf)
                                        <tr id="row-{{$cf->id}}" class="js-table-row">
                                            <td>
                                                <div class="form-group">
                                                    <label for="cf_name_{{$cf->id}}">{{__('a.Name')}}</label>
                                                    <input type="text"
                                                           class="form-control js-dynamic-cf-name-field"
                                                           id="cf_name_{{$cf->id}}"
                                                           name="cf_name"
                                                           value="{{$cf->meta_name}}"
                                                           autocomplete="off"
                                                           placeholder="{{__('a.Name')}}" required/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label for="cf_value_{{$cf->id}}">{{__('a.Value')}}</label>
                                                    <input type="text" class="form-control js-dynamic-cf-value-field"
                                                           id="cf_value_{{$cf->id}}"
                                                           name="cf_value"
                                                           value="{{$cf->meta_value}}"
                                                           autocomplete="off"
                                                           placeholder="{{__('a.Value')}}" required/>
                                                </div>
                                            </td>
                                            <td style="vertical-align: middle; padding-top: 0px;">
                                                <a href="#"
                                                   data-id="{{$cf->id}}"
                                                   title="{{__('a.Update')}}"
                                                   class="btn btn-primary btn-sm mr-2 mt-4 text-center js-link-update-cf">
                                                    <i class="fa fa-save mr-0"></i>
                                                </a>
                                                <a href="#"
                                                   data-id="{{$cf->id}}"
                                                   class="btn btn-danger btn-sm mr-2 mt-4 js-link-delete-cf"
                                                   title="{{__('a.Delete')}}">
                                                    <i class="fa fa-times mr-0"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="js-empty-row">
                                            <td>
                                                <div class="bs-component">
                                                    <div class="alert alert-info">
                                                        {{__('a.No custom fields yet.')}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- End .row --}}
@endif
{{-- END CUSTOM FIELDS --}}
