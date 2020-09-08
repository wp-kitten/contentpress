jQuery( function ($) {
    "use strict";

    $( '.btn-toggle-nav' ).on( 'click', function (ev) {
        ev.preventDefault();
        $( '.topnav' ).toggleClass( 'responsive' );
    } );

    $('.masonry-grid').masonry({
        // options
        itemSelector: '.masonry-item',
        columnWidth: '.grid-sizer',
        percentPosition: true,
    });


    var relatedPostsCarousel = $('.related-posts');
    if(relatedPostsCarousel && relatedPostsCarousel.length){
        var siemaCarousel = new Siema({
            selector: '.siema-slider',
            perPage: {
                768: 2,
                1024: 3,
            },
            loop: false
        });
        $('.btn-prev').on('click', function(ev){
            ev.preventDefault();
            siemaCarousel.prev();
        });
        $('.btn-next').on('click', function(ev){
            ev.preventDefault();
            siemaCarousel.next();
        });
    }
} );
