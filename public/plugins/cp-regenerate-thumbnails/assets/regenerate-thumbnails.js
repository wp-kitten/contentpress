jQuery( function ($) {
    "use strict";

    var locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
    if ( !locale ) {
        throw new Error( 'The AppLocale  was not found' );
    }
    var pageLocale = ( typeof ( window.RegenerateThumbnailsLocale ) !== 'undefined' ? window.RegenerateThumbnailsLocale : false );
    if ( !pageLocale ) {
        throw new Error( 'The RegenerateThumbnailsLocale was not found' );
    }

    var ProgressBar = {
        _progressWrap: null,
        _progressBar: null,
        _processedNumInfo: null,
        _submitButton: null,
        _doingAjax: false,
        _processedFiles: 0,
        //#! The number of files to process per request
        _batchCount: 2,
        _currentIndex: 0,
        _progress: 0,

        init: function () {
            this._progressWrap = $( '.js-cprt-progress-wrap' );
            this._progressBar = $( '.js-cprt-progress-bar' );
            this._processedNumInfo = $( '#js-cprt-processed-num' );
            this._submitButton = $( '.js-cprt-submit-button' );

            this._progress = Math.ceil( ( 1 * 100 ) / pageLocale.files );

            if ( pageLocale.images_count ) {
                this.__setupListeners();
            }
        },

        __setupListeners: function () {
            var self = this;
            this._submitButton.on( 'click', function (ev) {
                ev.preventDefault();

                $( this ).addClass( 'no-click' );

                //#! Display the progressbar
                self._progressWrap.removeClass( 'hidden' );
                self._processedNumInfo.text( 0 );

                //#! Initialize the ajax requests
                self.__process();
            } );
        },

        __process: function () {
            var self = this;

            if ( this._doingAjax ) {
                this._doingAjax = false;
                return false;
            }

            var batch = self.__getBatch();
            if ( batch.length < 1 ) {
                showToast( pageLocale.text_completed, 'success' );
                self._progressBar.removeClass( 'progress-bar-animated' );
                this._doingAjax = false;
                return false;
            }

            this._doingAjax = true;
            var ajaxConfig = {
                url: locale.ajax.url,
                method: 'POST',
                timeout: 29000,
                cache: false,
                async: true,
                data: {
                    action: 'cprt_regenerate_thumbnail',
                    files: batch,
                    [locale.nonce_name]: locale.nonce_value,
                }
            };

            $.ajax( ajaxConfig )
                .done( function (r) {
                    if ( r ) {
                        if ( r.success ) {
                            //#! Update UI
                            self._processedFiles += batch.length;

                            //#! Calculate percentage
                            if ( self._processedFiles.length > pageLocale.files.length ) {
                                self._processedFiles = pageLocale.files.length;
                            }
                            self._processedNumInfo.text( self._processedFiles );
                            var progress = ( ( self._processedFiles * 100 ) / pageLocale.files.length );

                            //#! Update progress bar
                            self._progressBar.css( 'width', progress + '%' ).attr( 'aria-valuenow', progress );

                            if ( r.data ) {
                                showToast( r.data, 'success' );
                            }
                        }
                        else {
                            if ( r.data ) {
                                showToast( r.data, 'warning' );
                            }
                            else {
                                showToast( locale.ajax.empty_response, 'warning' );
                            }
                        }
                    }
                    else {
                        showToast( locale.ajax.no_response, 'warning' );
                    }
                } )
                .fail( function (x, s, e) {
                    showToast( e, 'error' );
                } )
                .always( function () {
                    self._doingAjax = false;
                    self.__process();
                } );
        },

        __getBatch: function () {
            var self = this,
                batch = [],
                files = pageLocale.files.slice( self._currentIndex, self._currentIndex + self._batchCount );

            $.each( files, function (i, file) {
                batch.push( file );
            } );
            self._currentIndex += self._batchCount;
            return batch;
        }
    };

    ProgressBar.init();
} );
