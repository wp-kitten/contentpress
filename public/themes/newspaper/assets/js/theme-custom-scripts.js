jQuery( function ($) {
    "use strict";

    $('.load-container').fadeOut();
    $('.loader-mask').delay(450).fadeOut('600');

    //#! [Responsive] Toggle nav menu
    $( '.btn-toggle-nav' ).on( 'click', function (ev) {
        ev.preventDefault();
        $( '.topnav' ).toggleClass( 'responsive' );
    } );

    //#! News ticker
    $('.news-ticker-wrap').jConveyorTicker({
        anim_duration: 200,
        force_loop:true,
    });

    //#! Various places
    // $( '.masonry-grid' ).masonry( {
    //     // options
    //     itemSelector: '.masonry-item',
    //     columnWidth: '.grid-sizer',
    //     percentPosition: true,
    // } );

    //#! Singular: Related posts carousel
    var relatedPostsCarousel = $( '.related-posts' );
    if ( relatedPostsCarousel && relatedPostsCarousel.length ) {
        var siemaCarousel = new Siema( {
            selector: '.siema-slider',
            perPage: {
                768: 2,
                1024: 3,
            },
            loop: false
        } );
        $( '.btn-prev' ).on( 'click', function (ev) {
            ev.preventDefault();
            siemaCarousel.prev();
        } );
        $( '.btn-next' ).on( 'click', function (ev) {
            ev.preventDefault();
            siemaCarousel.next();
        } );
    }

    //#! Filter search results
    $( '#js-sort-results' ).on( 'change', function () {
        var formID = $( this ).attr( 'data-form-id' );
        if ( formID ) {
            $( '#' + formID ).trigger( 'submit' );
        }
    } );
} );
