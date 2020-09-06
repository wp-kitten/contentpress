jQuery(function($){
    "use strict";

    var editor = ace.edit("cpcs_custom_css");
    editor.setTheme("ace/theme/monokai");
    editor.session.setValue($('#cpcs_custom_css_textarea').val());
    editor.setShowPrintMargin(false);
    editor.session.setMode("ace/mode/css");

    //#! Auto sync hidden field with the editor data
    $('#cpcs_form').on('submit', function(ev){
        $( '#cpcs_custom_css_field' ).val( editor.session.getValue() );
        return true;
    });
});
