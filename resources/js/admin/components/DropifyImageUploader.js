class __DropifyImageUploader {

    constructor(element, dropifyOptions = {}) {
        const $this = this;

        this.element = element;
        this.dropifyOptions = window.$.extend( {}, {}, dropifyOptions );

        //#! Simple container to use to store ajax responses and share between callbacks
        this.ajaxResponse = {};

        this.dropifyField = this.element.dropify( dropifyOptions );
        this.dropifyField.on( 'dropify.afterClear', function (event, element) {
            $this.element.trigger( 'change' );
        } );
    }

    setup(callbacks = {}) {

        let _callbacks = {
            on_add_image: function ($this) {
            },
            on_remove_image: function ($this) {
            },
        };
        _callbacks = window.$.extend( {}, _callbacks, callbacks );

        if ( this.element ) {
            const $this = this;

            this.element.on( 'change', function (ev) {
                if ( $this.element.val().length < 1 ) {
                    _callbacks.on_remove_image( $this );
                }
                else {
                    _callbacks.on_add_image( $this );
                }
            } );
        }
    }
}

window.DropifyImageUploader = __DropifyImageUploader;
export default __DropifyImageUploader;
