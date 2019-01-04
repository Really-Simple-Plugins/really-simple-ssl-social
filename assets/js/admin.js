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
            $(this).closest('tr').show();
        });
    }

    //Visual feedback for 'Clear share cache' button'
    $(document).on('click', ".rsssl-button-clear-share-cache", function (e) {

        var btn = $(this);
        btn.html('<div class="rsssl-loading">Clearing....</div>');

    });

    function rsssl_check_custom_css() {
        if ($('option[name=rsssl_buttons_theme]').val("sidebar-dark") ||( $('option[name=rsssl_buttons_theme]').val("sidebar-color") ) ) {
            $('select[name=rsssl_button_position]').closest('tr').hide();
        } else {
            $('select[name=rsssl_button_position]').closest('tr').show();
        }
    }

    rsssl_check_custom_css();
    $(document).on("click", "#rsssl_use_custom_css", function () {
        rsssl_check_custom_css();
    });

<<<<<<< Updated upstream
=======
    console.log($('select[name=rsssl_sitewide_or_block]').val());

>>>>>>> Stashed changes
    function rsssl_show_button_position() {
        if ($('select[name=rsssl_sitewide_or_block]').val() ==="sitewide") {
            $('select[name=rsssl_button_position]').closest('tr').show();
        } else {
            $('select[name=rsssl_button_position]').closest('tr').hide();
        }
    }

    rsssl_show_button_position();
    $(document).on("change", "#sitewide_or_block", function () {
        rsssl_show_button_position();
    });

});