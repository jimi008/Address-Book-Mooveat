
jQuery( document ).ready(function( $ ) {

    $('.mv-contacts, .mv-social').wrapAll('<div class="col col-1"></div>');
    $('.mv-logo, .mv-cpo-grp, .mv-cso-grp, .mv-typologie ').wrapAll('<div class="col col-2"></div>');
    $('.mv-fonction, .mv-cp-grp, .mv-suivi_actions ').wrapAll('<div class="col col-3"></div>');

    var $followUpWrapper = $( ".mv-followup-wrap" );
    $followUpWrapper.dialog({
        autoOpen: false,
        draggable: false,
        modal: true,
        width: '100%',
        appendTo: '.col-3'
    });


    $( ".dialog-opener" ).click(function() {
        $followUpWrapper.dialog( "open" );

        var $nom = $('.mv-nom input').val();
        var $firstName = $('.mv-firstname input').val();
        if (!$nom == "" && !$firstName == "") {
            $('.mv-followup-wrap label').text('Contact: ' + $firstName + ' ' + $nom);
        }

    });



    });

// disable dragging and collapse
(function($){
    $(document).ready(function() {
        $('.postbox .hndle').unbind('click.postboxes');
        $('.postbox .handlediv').remove();
        $('.postbox').removeClass('closed');
    });
})(jQuery);





jQuery(document).ready(function($) {

    $('body').append('' +
        '<div class="post-loader">' +
        '<svg width="50px" height="50px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-ripple"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><g> <animate attributeName="opacity" dur="2s" repeatCount="indefinite" begin="0s" keyTimes="0;0.33;1" values="1;1;0"></animate><circle cx="50" cy="50" r="40" stroke="#8ECCC0" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="r" dur="2s" repeatCount="indefinite" begin="0s" keyTimes="0;0.33;1" values="0;22;44"></animate></circle></g><g><animate attributeName="opacity" dur="2s" repeatCount="indefinite" begin="1s" keyTimes="0;0.33;1" values="1;1;0"></animate><circle cx="50" cy="50" r="40" stroke="#FBBA30" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="r" dur="2s" repeatCount="indefinite" begin="1s" keyTimes="0;0.33;1" values="0;22;44"></animate></circle></g></svg>' +
        '</div>');

    // Add default 'Select one'
    $( '.mv-cpo select option[value=0]' ).attr({ selected: 'selected', disabled: 'disabled'});

    /**
     * Get country option on select menu change
     *
     */
    $( '.mv-cpo select' ).change(function () {

        var selected_cpo = ''; // Selected value
        var selected_category = ''; //Selected option title

        // Get selected value
        $( '.mv-cpo select option:selected' ).each(function() {
            selected_cpo += $( this ).val();
            selected_category += $( this ).text();
        });

        //disable select default
        $( '.mv-cso select' ).attr( 'disabled', 'disabled' );

        // If default is not selected get categories
        if( selected_category != 'Select Category' ) {

            // Send AJAX request
            data = {
                action: 'cso_category',
                cpo_nonce: cpo_vars.cpo_nonce,
                selected_cpo: selected_cpo
            };

            // Get response and populate select field
            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'POST',
                success: function (data) {
                    if (data) {

                        // Disable 'Select Area' field until principal category is selected
                        $('.mv-cso select').html($('<option></option>').val('0').html('Select Category').attr({
                            selected: 'selected',
                            disabled: 'disabled'
                        }));

                        // Add secondary organizations to select field options
                        $.each(data, function (val, text) {

                            $.each(this, function (v, t) {
                                /// do stuff
                                $('.mv-cso select').append($('<option></option>').val(v).html(t));
                            });

                        });

                        // Enable 'Select Area' field
                        $('.mv-cso select').removeAttr('disabled');
                    }
                },
                complete: function (xhr) {
                    $(".post-loader").css("display", "none");
                }
            });
        }
    });
});



jQuery(document).ready(function($) {

    $(".mv-cso select").each(function () {
        this.disabled = $('option', this).length < 2;
    });
});



jQuery(document).ready(function($) {


});
