jQuery( document ).ready(function( $ ) {

    // $('.mv-contacts, .mv-social').wrapAll('<div class="col col-1"></div>');
    // $('.mv-logo, .mv-cpo-grp, .mv-cso-grp, .mv-typologie ').wrapAll('<div class="col col-2"></div>');
    // $('.mv-fonction, .mv-cp-grp, .mv-suivi_actions ').wrapAll('<div class="col col-3"></div>');

    var $followUpWrapper = $( ".mv-followup-wrap" );
    $followUpWrapper.dialog({
        autoOpen: false,
        draggable: false,
        modal: true,
        width: '85%',
        appendTo: '#acf-group_5ad78f288b311 .inside.acf-fields'
    });

    $( ".dialog-opener" ).click(function() {
        $followUpWrapper.dialog( "open" );

        var $nom = $('.mv-nom input').val();
        var $firstName = $('.mv-firstname input').val();
        if (!$nom == "" && !$firstName == "") {
            $('.mv-followup-wrap > .acf-label label').text('Contact: ' + $firstName + ' ' + $nom);
        }

    });

// disable dragging and collapse
    $('.postbox .hndle').unbind('click.postboxes');
    $('.postbox .handlediv').remove();
    $('.postbox').removeClass('closed');


    $('body').append('' +
        '<div class="post-loader">' +
        '<svg width="50px" height="50px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-ripple"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><g> <animate attributeName="opacity" dur="2s" repeatCount="indefinite" begin="0s" keyTimes="0;0.33;1" values="1;1;0"></animate><circle cx="50" cy="50" r="40" stroke="#8ECCC0" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="r" dur="2s" repeatCount="indefinite" begin="0s" keyTimes="0;0.33;1" values="0;22;44"></animate></circle></g><g><animate attributeName="opacity" dur="2s" repeatCount="indefinite" begin="1s" keyTimes="0;0.33;1" values="1;1;0"></animate><circle cx="50" cy="50" r="40" stroke="#FBBA30" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="r" dur="2s" repeatCount="indefinite" begin="1s" keyTimes="0;0.33;1" values="0;22;44"></animate></circle></g></svg>' +
        '</div>');
    $(".post-loader").css("display", "none");

    // Add default 'Select one'
    if(!$( '.mv-cpo select option:selected' ).length){
        $( '.mv-cpo select option[value=""]' ).attr({ selected: 'selected',});
        $(".post-loader").css("display", "none");
    }

    var catLevelClasses = ['.mv-cpo','.mv-cso','.mv-cto','.mv-cl4','.mv-cl5','.mv-cl6'];

    // add contact to organisation button
    var orga_query_string = "mv_orga=";
    if(window.location.href.indexOf(orga_query_string)!=-1){
        $.each(catLevelClasses,function(index,val){
            $(val+' select')
                .prop({
                    disabled:false
                })
                .find('option')
                .attr({
                    selected: 'selected'
                })
        });
    }

    // nomenclature level fields in contact edit page
    $.each(catLevelClasses,function(index,val){
        $(val+' select').change(function(){
            if(catLevelClasses[catLevelClasses.length-1]!=val){
                var selected_val = '',
                    selected_cat_title = '';

                $(val+' select option:selected').each(function(){
                    selected_val = $(this).val();
                    selected_cat_title = $(this).text();
                })

                for(var i = index+1;i<catLevelClasses.length;i++){
                    reset_fields([catLevelClasses[i]]);
                }

                $('.mv-ab-orga-name-nomenclature select').html($('<option></option>').val('').html('Choisir').prop({
                    'selected': false
                }));


                if(selected_cat_title != 'Choisir' && selected_cat_title != ''){
                    $(".post-loader").css("display", "block");
                    // Send AJAX request
                    var data = {
                        action: 'catpro_category',
                        catpro_nonce: catpro_vars.catpro_nonce,
                        selected_catpro: selected_val,

                        cat_level: index+1
                    };


                    // Get response and populate select field
                    $.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        success: function (data) {
                            set_field_options(data,catLevelClasses[index+1]);
                            $(".post-loader").css("display", "block");
                        },
                        complete: function (xhr) {
                            var data = $.parseJSON(xhr.responseText);
                            if(data != null){
                                var firstDataVal = '';
                                $.each(data[0],function(key,val){
                                    firstDataVal = val;
                                    return false;
                                });
                                //console.log(data.length + '; ' + firstDataVal);
                                //console.log(data[0]);

                                if(data.length!==1 || firstDataVal!=='-'){
                                    //console.log('hide loader');
                                    $(".post-loader").css("display", "none");
                                }
                                else{
                                    //console.log('show loader');
                                    $(".post-loader").css("display", "block");
                                }
                            }
                            else{

                                $(".post-loader").css("display", "none");
                            }
                        }
                    });
                }
            }
        });
    });

    function set_field_options(data,fieldClass){
        //console.log(data);
        if (typeof data !== "undefined") {

            if(data ==  null || data.length == 0) {
                $(fieldClass + ' select').prop({disabled:false}).html($('<option></option>').val('').html(''));
            }
            else{
                $(fieldClass + ' select').html($('<option></option>').val('').html('Choisir').attr({
                    selected: 'selected'
                }));
            }

            if(data != null){
                if(data.length>1){
                    // Add pro categories to select field options
                    $.each(data, function (val, text) {
                        $.each(this, function (v, t) {

                            /// do stuff
                            $(fieldClass + ' select').append($('<option></option>').val(v).html(t));
                        });
                    });
                }
                else if(data.length == 1){
                    $.each(data, function (val, text) {
                        $.each(this, function (v, t) {
                            /// do stuff
                            $(fieldClass + ' select').html($('<option></option>').val(v).html(t).attr({
                                selected: 'selected'
                            }));
                            $(".post-loader").css("display", "block");
                            $(fieldClass + ' select').change();
                        });
                    });
                }
            }
 // Enable 'Select Area' field
            if($(fieldClass + ' select').find('option').length>1){
                $(fieldClass + ' select').prop({disabled:false});
            }
        } else {
            $(fieldClass + ' select').prop({disabled:false}).html($('<option></option>').val('').html(''));
        }
    }

    function reset_fields(classArray){
        $.each(classArray,function(index,val){
            $(val+' select').find('option')
                .remove()
                .end()
                .append('<option value=""></option>')
                .prop({disabled:false})
                .val('');
        });
    }

    // organisation name autocomplete
    $('.mv-ab-orga-name-nomenclature select').change(function(){
        var selected_val = $(this).val();
        $('.organisation-name-to-be-added input').val('');
        var data = {
            action: 'mv_ab_prev_cats',
            catpro_nonce: catpro_vars.catpro_nonce,
            selected_catpro: selected_val,
        };
        // Get response and populate select field
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            success: function (data) {
                console.log(data);
                if(data!=null){
                    $.each(catLevelClasses,function(index,val){

                        $(val + ' select').prop({disabled:false});

                        if(val != '.mv-cl6' && val != ".mv-cpo"){
                            $(val + ' select').html($('<option></option>').val(data[index]['id']).html(data[index]['name']).attr({
                                selected: 'selected'
                            }));
                        }
                        else if(val == '.mv-cpo'){
                            $(val + ' select').val(data[index]['id']);
                        }
                    });
                    $('.mv-cat-orga-grp.mv-classification-grp').animate({
                        'opacity': 0.5
                    });
                }
            },
        });

        $('.mv-cl6 select').html($('<option></option>').val(selected_val).html($(this).find('option:selected').text()).attr({
            selected: 'selected'
        }));

    });

    // not found orga name text input
    $('.organisation-name-to-be-added input')
        .on('keypress', function(event) {

            $('.mv-cat-orga-grp.mv-classification-grp').animate({
                'opacity': 1
            });

            $('.mv-ab-orga-name-nomenclature select').html($('<option></option>').val('').html('Choisir').prop({
                'selected': false
            }));
            if($(this).val() == ''){
                $(catLevelClasses[0] + ' select').prop({disabled:false}).val('');
                for(var i = 1;i<catLevelClasses.length;i++){
                    reset_fields([catLevelClasses[i]]);
                }
            }
            //$('.mv-ab-orga-name-nomenclature select').change();
            $(catLevelClasses[0] + ' select').prop({disabled:false});
        })



    // add reset button to contacts list table filters
    if($('.post-type-mv_address_book').length & $('.wp-list-table').length){
        $('#posts-filter .tablenav.top .actions').last().append('<a class="ab-reset-button button">Reset</a>');

        $('body').on("click",".ab-reset-button",function(){
            window.location = window.location.protocol + "//" + window.location.hostname + '/wp-admin/edit.php?post_type=' + 'mv_address_book';
        });
    }

    // init select 2
    if( $( '.multi-select' ).length > 0 ) {

        $( '.multi-select' ).select2({
            placeholder: 'Select'
        });
        // $( document.body ).on( "click", function() {
        //     $( '.multi-select' ).select2({
        //         placeholder: 'Select'
        //     });
        // });

    }

// nomenclature level fields in followup org page
var catLevelClasses_f = ['.cat1', '.cat2', '.cat3', '.cat4', '.cat5'];    

$.each(catLevelClasses_f, function(index, val) {
        $(val + ' select').change(function() {
            var tr = $(this).closest('tr');
            if (catLevelClasses_f[catLevelClasses_f.length - 1] != val) {
                var selected_val = '',
                selected_cat_title = '';
                tr.find(val + ' select option:selected').each(function() {
                    selected_val = $(this).val();
                    // console.log(selected_val);
                    selected_cat_title = $(this).text();
                })
                for (var i = index + 1; i < catLevelClasses_f.length; i++) {
                    reset_fields_f( [catLevelClasses_f[i]], tr );
                }
                // $('.mv-ab-orga-name-nomenclature select').html($('<option></option>').val('').html('Choisir').prop({
                //     'selected': false
                // }));
                if (selected_cat_title != '- Choisir -' && selected_cat_title != '') {
                    $(".post-loader").css("display", "block");
                    // Send AJAX request
                    var data = {
                        action: 'catpro_category',
                        catpro_nonce: catpro_vars.catpro_nonce,
                        selected_catpro: selected_val,
                        cat_level: index + 1
                    };
                    // Get response and populate select field
                    $.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        success: function(data) {
                            set_field_options_f(data, catLevelClasses_f[index + 1], tr);
                            $(".post-loader").css("display", "block");
                        },
                        complete: function(xhr) {
                            var data = $.parseJSON(xhr.responseText);
                            if (data != null) {
                                var firstDataVal = '';
                                $.each(data[0], function(key, val) {
                                    firstDataVal = val;
                                    return false;
                                });
                                //console.log(data.length + '; ' + firstDataVal);
                                //console.log(data[0]);
                                if (data.length !== 1 || firstDataVal !== '-') {
                                    //console.log('hide loader');
                                    $(".post-loader").css("display", "none");
                                } else {
                                    //console.log('show loader');
                                    $(".post-loader").css("display", "block");
                                }
                            } else {
                                $(".post-loader").css("display", "none");
                            }
                        }
                    });
                }
            }
        });
    });

    function set_field_options_f(data, fieldClass, tr) {
        //console.log(data);
        if (typeof data !== "undefined") {
            if (data == null || data.length == 0) {
                tr.find(fieldClass + ' select').prop({
                    disabled: true
                }).html($('<option></option>').val('').html(''));
            } else {
                tr.find(fieldClass + ' select').html($('<option></option>').val('').html('- Choisir -').attr({
                    selected: 'selected'
                }));
            }
            if (data != null) {
                if (data.length > 1) {
                    // Add pro categories to select field options
                    $.each(data, function(val, text) {
                        $.each(this, function(v, t) {
                            /// do stuff
                            tr.find(fieldClass + ' select').append($('<option></option>').val(v).html(t));
                        });
                    });
                } else if (data.length == 1) {
                    $.each(data, function(val, text) {
                        $.each(this, function(v, t) {
                            /// do stuff
                            tr.find(fieldClass + ' select').html($('<option></option>').val(v).html(t).attr({
                                selected: 'selected'
                            }));
                            $(".post-loader").css("display", "block");
                            tr.find(fieldClass + ' select').change();
                        });
                    });
                }
            }
            // Enable 'Select Area' field
            if ( tr.find(fieldClass + ' select').find('option').length > 1 ) {
                tr.find(fieldClass + ' select').prop({
                    disabled: false
                });
            }
        } else {
            tr.find(fieldClass + ' select').prop({
                disabled: true
            }).html($('<option></option>').val('').html(''));
        }
    }

    function reset_fields_f(classArray, tr) {
        $.each(classArray, function(index, val) {
            tr.find(val + ' select').find('option').remove().end().append('<option value=""></option>').prop({
                disabled: true
            }).val('');
        });
    }


    if (typeof acf !== "undefined") {
        // get localized data
        var postID = acf.get('post_id');
        acf.add_filter('select2_ajax_results', function(json, params, instance) {
            if (json['results']) {
                $.map(json['results'], function(obj) {
                    if (obj['text']) {
                        obj['text'] = obj['text'].replace(/(- )*/g, "");
                    }
                    return obj;
                });
            }
            // return
            return json;
        });
    }

// organization follow-up table status query 

// Toogle display Org rename input and submit button
    $(".rename-org").click(function() {
        var orginput_wrap = $(this).closest('.column-action').find('.orginput-wrap');
        orginput_wrap.toggle("slow");
    });


// Rename org name 
    $('.orgname-submit').click(function () {
        // body...
        var org_id = $(this).attr('data-org-id');
        var org_new_name = $(this).closest('td').find('.orgname-input').val();
        var $tr = $(this).closest('tr');


        var data = {
            action: 'mvab_followup',
            org_followup_nonce: org_followup_vars.org_followup_nonce,
            org_id: org_id,
            org_new_name: org_new_name,
        };

        $(".post-loader").css("display", "block");
        // Get response and 
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            success: function(data) {
                console.log(data);
                
                if (data != null) {
                    // console.log(data);
                    $tr.find('.column-org_name').html(org_new_name);

                }
            },
            complete: function(xhr) {
                $(".post-loader").css("display", "none");
            }
        });   

    });
       
    
//If status valid  function
    function valid_status($this) {
        var selected_val = $this.val();
        // alert(selected_val);
        var status_button = $this.closest('tr').find('.status-action');
        if (selected_val == 'valid') {
            status_button.hide();
        } else {
            status_button.show();
        }
    }

//if status refused function
    function refused_status($this) {
        var selected_val = $this.val();
        var status_button = $this.closest('tr').find('.status-action');
        if (selected_val == 'refused') {
            status_button.addClass('warning-btn');
            status_button.val('Confirmer le refus');
            status_button.removeAttr("disabled");
            
        } else {
            status_button.removeClass('warning-btn');
            status_button.prop('disabled', true);
            status_button.val('statut à définir');
        }
    }
    $org_status = $('.org-status select');
    $org_status.each(function() {
        valid_status($(this));
        refused_status($(this));
    });


//Save organization status in org (term level 6)
    $('.org-status select').change(function() {

        var selected_val = $(this).val();
        valid_status($(this));
        refused_status($(this));

        var org_id = $(this).attr('data-org-id');
        var data = {
            action: 'mvab_followup',
            org_followup_nonce: org_followup_vars.org_followup_nonce,
            selected_status: selected_val,
            org_id: org_id,
        };


        $(".post-loader").css("display", "block");

        // Get response and 
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            success: function(data) {
                console.log(data);
                if (data != null) {
                    // console.log(data);
                    $(".post-loader").css("display", "none");
                }
            },
        });
    });


//Delete Organization - Followup
    $('.status-action').click(function () {
        // body...
        var org_id = $(this).attr('data-org-id');
        var $tr = $(this).closest('tr')

        // alert(org_id);
        

        var data = {
            action: 'mvab_followup',
            org_followup_nonce: org_followup_vars.org_followup_nonce,
            org_id: org_id,
            org_del: true,
        };

        $(".post-loader").css("display", "block");

        // Get response and 
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            beforeSend:function(){
             return confirm("Are you sure?");
            },            
            success: function(data) {
                console.log(data);
                if (data != null) {
                    // console.log(data);

                    $tr.fadeOut(100,function(){ 
                            $tr.remove();                    
                    }); 


                    $(".post-loader").css("display", "none");
                }
            },
        });        

    });


//If categories/classifications (term levels) dropdown change - Followup
    $('.cat-lvl').change(function() {

        var cat_lvl_action = $(this).closest('tr').find('.cat-action');

        cat_lvl_action.show().removeAttr("disabled");

    });

    $('.cat-action').click(function(){

        var org_id = $(this).attr('data-org-id');
        var $tr = $(this).closest('tr')

        // alert(org_id);


       var cat_values = {};
       $tr.find('.cat-lvl').each(function(){
            var cat = $(this).attr('id');
            var val = $('option:selected',this).val();
            cat_values[cat] = val;
       });

        // console.log(cat_values);
        var myJSONText = JSON.stringify( cat_values );
        // console.log(myJSONText);
        
        var data = {
            action: 'mvab_followup',
            org_followup_nonce: org_followup_vars.org_followup_nonce,
            org_id: org_id,
            org_lvl_change: true,
            cat_values: myJSONText,
        };

        $(".post-loader").css("display", "block");

        // Get response and 
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            beforeSend:function(){
             return confirm("Are you sure?");
            },            
            success: function(data) {
                console.log(data);
                if (data != null) {

                    $(".post-loader").css("display", "none");
                }
            },
        });   

    })


}); //end document.ready


// jQuery(document).ready(function($) {
//
//     // disable CSO field if no options
//     // $(".mv-cso select").each(function () {
//     //     this.disabled = $('option', this).length < 2;
//     // });
//
//     // disable CTO field if no options
//     // $(".mv-cto select").each(function () {
//     //     this.disabled = $('option', this).length < 2;
//     //
//     // });
//

// });
