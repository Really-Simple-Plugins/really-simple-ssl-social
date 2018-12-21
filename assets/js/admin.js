jQuery(document).ready(function ($) {
    'use strict';

    //Show options according to button type (existing, builtin or native)
    rsssl_show_options();
    $(document).on('change', 'select[name=rsssl_button_type]',function(){
        rsssl_show_options();
    });

    function rsssl_show_options()
    {
        $('.button_type').each(function(){
            $(this).closest('tr').hide();
        });

        var selected = $('select[name=rsssl_button_type]').val();
        $('.'+selected).each(function(){
            console.log(selected);
            $(this).closest('tr').show();
        });
    }

    //Visual feedback for 'Clear share cache' button'
    $(document).on('click', ".rsssl-button-clear-share-cache", function (e) {

        var btn = $(this);
        btn.html('<div class="rsssl-loading">Clearing....</div>');

    });

    function rsssl_uses_sidebar_theme() {

        if ( ($('select[name=rsssl_buttons_theme]').val() === 'sidebar-dark' ) || ($('select[name=rsssl_buttons_theme]').val() === 'sidebar-color') ) {
            console.log("sidebar");
            $('select[name=rsssl_button_position]').closest('tr').hide();
        } else {
            console.log("no sidebar");
            $('select[name=rsssl_button_position]').closest('tr').show();
        }
    }

    rsssl_uses_sidebar_theme();
    $(document).on("change", 'select[name=rsssl_buttons_theme]', function () {
        rsssl_uses_sidebar_theme();
    })

});