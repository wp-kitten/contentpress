jQuery( function ($) {
    "use strict";

    $( '.load-container' ).fadeOut();
    $( '.loader-mask' ).delay( 450 ).fadeOut( '600' );

    //#! [Responsive] Toggle nav menu
    $( '.js-toggle-menu' ).on( 'click', function (ev) {
        ev.preventDefault();
        $( '.topnav' ).toggleClass( 'responsive' );
    } );

    //#! News ticker
    var newsTicker = $( '.news-ticker-wrap' ),
        ntItems = $( 'li', newsTicker );
    if ( ntItems && ntItems.length ) {
        newsTicker.jConveyorTicker( {
            anim_duration: 200,
            force_loop: true,
        } );
    }

    //#! Creates a siema carousel
    var createSiemaCarousel = function ($sliderWrap, sliderSelector, $objPerPage, loop) {
        var siemaCarousel = new Siema( {
            selector: sliderSelector,
            perPage: $objPerPage,
            loop: loop
        } );
        $( '.btn-prev', $sliderWrap ).on( 'click', function (ev) {
            ev.preventDefault();
            siemaCarousel.prev();
        } );
        $( '.btn-next', $sliderWrap ).on( 'click', function (ev) {
            ev.preventDefault();
            siemaCarousel.next();
        } );
        return siemaCarousel;
    };

    //#! Various places
    $( '.masonry-grid.js-masonry-init' ).masonry( {
        // options
        itemSelector: '.masonry-item',
        columnWidth: '.grid-sizer',
        percentPosition: true,
    } );

    //#! Singular: Related posts carousel
    var relatedPostsCarousel = $( '.related-posts' );
    if ( relatedPostsCarousel && relatedPostsCarousel.length ) {
        $.each(relatedPostsCarousel, function(i, el){
            var carousel = createSiemaCarousel( $(el), '.siema-slider', {
                768: 2,
                1024: 3,
            }, false );
        });

    }

    //#! Filter search results
    $( '#js-sort-results' ).on( 'change', function () {
        var formID = $( this ).attr( 'data-form-id' );
        if ( formID ) {
            $( '#' + formID ).trigger( 'submit' );
        }
    } );

    //#! Side nav
    var sideNav = $( '.sidenav' );
    $( '.btn-open-sidenav' ).on( 'click', function (ev) {
        ev.preventDefault();
        sideNav.css( 'width', '250px' );
    } );
    $( '.btn-close-sidenav' ).on( 'click', function (ev) {
        ev.preventDefault();
        sideNav.css( 'width', 0 );
    } );
} );
