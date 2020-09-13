jQuery(function($){
    "use strict";

    $('#featured-categories-field').selectize({
        create: false,
        sortField: 'text',
        maxItems: 15,
    });
})
