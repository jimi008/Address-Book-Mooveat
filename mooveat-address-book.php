<?php
/*
Plugin Name:  Mooveat Address Book
Plugin URI:   http://mooveat.io
Description:  Manage and modify address book with help of ACF
Version:      1.0.0
Author:       Zahir Ahmed
Author URI:   http://mooveat.io
License:      GPLv3
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


//require_once plugin_dir_path( __FILE__ ) . 'includes/register-acf.php';
//require_once plugin_dir_path( __FILE__ ) . 'includes/register-admin-columns.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-mooveat-address-book.php';

function run_mooveat_address_book() {

    $spmm = new Mooveat_Address_Book();
    $spmm->run();

}

run_mooveat_address_book();



function my_restrict_manage_posts($post_type) {

    global $post_type;

    if('mv_address_book' !== $post_type){
        return; //check to make sure this is your cpt
    }

    $selected = '';
    $request_attr = 'mv_cpo';
    if ( isset($_REQUEST[$request_attr] ) ) {
        $selected = $_REQUEST[$request_attr];
    }

    $allcpo = $allcso = array();

    $args = array(
        'numberposts'	=> -1,
        'post_type'		=> 'mv_address_book'
    );

    $contacts_query = new WP_Query( $args );

    if ( $contacts_query->have_posts() ) {
        while ($contacts_query->have_posts()) {
            $contacts_query->the_post();

            $cpo_group = get_field('mvcpo');
            $cpo = $cpo_group['categorie_principale_organisation'];
            $allcpo[ $cpo['label'] ] = $cpo['value'];

            $cso_group = get_field('cso_grp');
            $cso = $cso_group['categorie_secondaire_organisation'];
            $allcso[ $cso ] = $cso;


            $cpg_group = get_field('categories_produits_grp');
            $cpg = $cpg_group['categories_produits'];
            foreach ($cpg as $cpg_single) {
                $allcsg[ $cpg_single ] = $cpg_single;
            }


            $label_group = get_field('label_grp');
            $label = $label_group['label'];
            foreach ($label as $label_single) {
                $labels[ $label_single ] = $label_single;
            }


//            $key = get_post_meta( get_the_ID(), 'cp_values', true );
//            var_dump($key);

        }
    }

        /* Restore original Post Data */
        wp_reset_postdata();


        ?>
        <select name="mv_cpo" style="width:10%;">
            <option value=""><?php _e('Any CPO', 'mv'); ?></option>
            <?php

            $current_s = isset($_GET['mv_cpo'])? $_GET['mv_cpo']:'';

            foreach ($allcpo as $label => $value) {
                printf (
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value == $current_s? ' selected="selected"':'',
                    $label
                );
            }
            ?>
        </select>

    <select name="mv_cso" style="width:10%;">
        <option value=""><?php _e('Any CSO', 'mv'); ?></option>
        <?php

        $current_s = isset($_GET['mv_cpo'])? $_GET['mv_cso']:'';

        foreach ($allcso as $label => $value) {
            printf (
                '<option value="%s"%s>%s</option>',
                $value,
                $value == $current_s? ' selected="selected"':'',
                $label
            );
        }
        ?>
    </select>

    <select name="mv_cpg[]" class="multi-select" multiple>
        <option value=""><?php _e('Any Catégorie produit', 'mv'); ?></option>
        <?php

        $current_s = isset($_GET['mv_cpg'])? $_GET['mv_cpg']:'';

        foreach ($allcsg as $label => $value) {
            ?>
            <option value="<?php echo $value; ?>" <?php echo in_array($value, $current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>


    <select name="mv_label[]" class="multi-select" multiple>
        <option value=""><?php _e('Any Label', 'mv'); ?></option>
        <?php

        $current_s = isset($_GET['mv_label'])? $_GET['mv_label']:'';

        foreach ($labels as $label => $value) {

            ?>
            <option value="<?php echo $value; ?>" <?php echo in_array($value, $current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>

        <?php

\    /** if submitted filter by post meta */
    add_filter( 'parse_query', 'wpse45436_posts_filter' );

    function wpse45436_posts_filter( $query ){

        global $pagenow; $type = 'mv_address_book';

        if (isset($_GET['post_type'])) { $type = $_GET['post_type']; }

        if ( 'mv_address_book' == $type && is_admin()  && $pagenow=='edit.php' ) {

            $qv = &$query->query_vars;
            $qv['meta_query'] = array();

            $mv_cpo = $_GET['mv_cpo'];
            if( !empty( $mv_cpo ) ) {
                $qv['meta_query'][] = array(
                    'key' => 'mvcpo_categorie_principale_organisation',
                    'value' => $mv_cpo,
                    'compare' => '='
                );
            }

            $mv_cso = $_GET['mv_cso'];
            if( !empty( $mv_cso ) ) {
                $qv['meta_query'][] = array(
                    'key' => 'cso_grp_categorie_secondaire_organisation',
                    'value' => $mv_cso,
                    'compare' => '='
                );
            }

            $mv_cpg = $_GET['mv_cpg'];
            if( !empty( $mv_cpg ) ) {

                $qv['meta_query'][] = array(
                    'key'		=> 'cp_values',
                    'value'		=> $mv_cpg,
                    'compare'	=> 'IN',

                );

            }



            $mv_label = $_GET['mv_label'];

            if( !empty( $mv_label ) ) {
                $qv['meta_query'][] = array(
                    'key' => 'label_values',
                    'value' => $mv_label,
                    'compare' => 'IN'
                );
            }


        }
    }
}
add_action('restrict_manage_posts','my_restrict_manage_posts');


add_filter('acf/save_post', 'convert_classes_to_standard_wp', 20);
function convert_classes_to_standard_wp($post_id) {
    // use a different field name for your converted value
    $cp_meta_key = 'cp_values';
    $label_meta_key = 'label_values';
    // clear any previously stored values
    delete_post_meta($post_id, $cp_meta_key);
    delete_post_meta($post_id, $label_meta_key);
    // get new acf value

    $cp_group = get_field('categories_produits_grp', $post_id);
    $cp_values = $cp_group['categories_produits'];


    if (is_array($cp_values) && count($cp_values) ) {
        foreach ($cp_values as $value) {
            add_post_meta($post_id, $cp_meta_key, $value, false);
        }
    }

    $label_group = get_field('label_grp', $post_id);
    $label_values = $label_group['label'];

    if (is_array($label_values) && count($label_values) ) {
        foreach ($label_values as $value) {
            add_post_meta($post_id, $label_meta_key, $value, false);
        }
    } // end if

}


