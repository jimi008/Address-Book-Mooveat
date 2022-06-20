<?php
/*
Plugin Name:  Mooveat Address Book
Plugin URI:   http://mooveat.io
Description:  Manage and modify address book with help of ACF
Version:      1.0.0
Author:       Jamil Ahmed
Author URI:   http://mooveat.io
License:      GPLv3
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


require_once plugin_dir_path( __FILE__ ) . 'includes/register-acf.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/register-admin-columns.php';
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

//    $selected = '';
//    $request_attr = 'mv_cpo';
//    if ( isset($_REQUEST[$request_attr] ) ) {
//        $selected = $_REQUEST[$request_attr];
//    }

    $allcpo = $allcso = $allcto = array();

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
            if (!empty($cpo)) {
                $allcpo[ $cpo['label'] ] = $cpo['value'];
            }


            $cso_group = get_field('cso_grp');
            $cso = $cso_group['categorie_secondaire_organisation'];
//            var_dump($cso);
            if (!empty($cso)) {
                $allcso[ $cso['label'] ] = $cso['value'];
            }


            $cto_group = get_field('cto_grp');
            $cto = $cto_group['categorie_tertiaire_organisation'];
            if (!empty($cto)) {
                $allcto[ $cto ] = $cto;
            }


            $cpg_group = get_field('categories_produits_grp');
            $cpg = $cpg_group['categories_produits'];

            if (!empty($cpg)) {
                foreach ($cpg as $cpg_single) {
                    $allcsg[ $cpg_single ] = $cpg_single;
                }
            }



            $label_group = get_field('label_grp');
            $label = $label_group['label'];

            if (!empty($label)) {
                foreach ($label as $label_single) {
                    $labels[ $label_single ] = $label_single;
                }
            }

//            $key = get_post_meta( get_the_ID(), 'cp_values', true );
//            var_dump($key);

        }
    }

        /* Restore original Post Data */
        wp_reset_postdata();


        ?>

    <label for="mv_cpo" class="screen-reader-text">Catégorie principale</label>
        <select name="mv_cpo[]" id="mv_cpo" class="multi-select" style="width:180px;" multiple placeholder="Catégorie principale">
<!--            <option value="">--><?php //_e('Catégorie principale', 'mv'); ?><!--</option>-->
            <?php

            $current_s = isset($_GET['mv_cpo'])? $_GET['mv_cpo']:'';
            if ($current_s) {
                foreach ( $current_s as $key => $value ){
                    $current_s[] = $value;
                }
            }



            foreach ($allcpo as $label => $value) {

                if ( is_array($current_s) ) {
                    if ( in_array($value, $current_s) ){
                        $current_s = true;
                    }
                }

                ?>
                <option value="<?php echo $value; ?>" <?php echo ($current_s) ? 'selected="selected"' : ""; ?> >
                    <?php echo $label ?>
                </option>

                <?php
            }
            ?>
        </select>


    <label for="mv_cso" class="screen-reader-text">Catégorie secondaire</label>
    <select name="mv_cso[]" id="mv_cso" class="multi-select" style="width:180px;" multiple placeholder="Catégorie secondaire">
<!--        <option value="">--><?php //_e('Catégorie secondaire', 'mv'); ?><!--</option>-->
        <?php
//        $current_s = array();
        $current_s = isset($_GET['mv_cso'])? $_GET['mv_cso']:'';
        if ($current_s) {
            foreach ( $current_s as $key => $value ){
                $current_s[] = $value;
            }
        }


        foreach ($allcso as $label => $value) {

            if ( is_array($current_s) ) {
                if ( in_array($value, $current_s) ){
                    $current_s = true;
                }
            }

            ?>
            <option value="<?php echo $value; ?>" <?php echo ($current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>



    <label for="mv_cto" class="screen-reader-text">Catégorie tertiaire</label>
    <select name="mv_cto[]" id="mv_cto" class="multi-select" style="width:180px;" multiple placeholder="Catégorie tertiaire">
<!--        <option value="">--><?php //_e('Catégorie tertiaire', 'mv'); ?><!--</option>-->
        <?php

        $current_s = isset($_GET['mv_cto'])? $_GET['mv_cto']:'';
        if ($current_s) {
            foreach ( $current_s as $key => $value ){
                $current_s[] = $value;
            }
        }

        foreach ($allcto as $label => $value) {

            if ( is_array($current_s) ) {
                if ( in_array($value, $current_s) ){
                    $current_s = true;
                }
            }

            ?>
            <option value="<?php echo $value; ?>" <?php echo ($current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>



    <label for="mv_cpg" class="screen-reader-text">Catégorie produit</label>
    <select name="mv_cpg[]" id="mv_cpg" class="multi-select" style="width:180px;" multiple placeholder="Catégorie produit">
<!--        <option value="">--><?php //_e('Catégorie produit', 'mv'); ?><!--</option>-->
        <?php

        $current_s = isset($_GET['mv_cpg'])? $_GET['mv_cpg']:'';
        if ($current_s) {
            foreach ( $current_s as $key => $value ){
                $current_s[] = $value;
            }
        }

        foreach ($allcsg as $label => $value) {

            if ( is_array($current_s) ) {
                if ( in_array($value, $current_s) ){
                    $current_s = true;
                }
            }

            ?>
            <option value="<?php echo $value; ?>" <?php echo ($current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>



    <label for="mv_label" class="screen-reader-text">Label</label>
    <select name="mv_label[]" id="mv_label" class="multi-select" style="width:180px;" multiple placeholder="Label">
<!--        <option value="">--><?php //_e('Label', 'mv'); ?><!--</option>-->
        <?php

        $current_s = isset($_GET['mv_label'])? $_GET['mv_label']:'';
        if ($current_s) {
            foreach ( $current_s as $key => $value ){
                $current_s[] = $value;
            }
        }

        foreach ($labels as $label => $value) {

            ?>
            <option value="<?php echo $value; ?>" <?php echo ($current_s) ? 'selected="selected"' : ""; ?> >
                <?php echo $label ?>
            </option>

            <?php
        }
        ?>
    </select>


        <?php

    /** if submitted filter by post meta */
    add_filter( 'parse_query', 'wpse45436_posts_filter' );

    function wpse45436_posts_filter( $query ){

        global $pagenow; $type = 'mv_address_book';

        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        if ( 'mv_address_book' == $type && is_admin()  && $pagenow=='edit.php' ) {

            $qv = &$query->query_vars;
            $qv['meta_query'] = array();

            $mv_cpo = isset( $_GET['mv_cpo'] ) ? $_GET['mv_cpo'] : '';
            if( !empty( $mv_cpo ) && isset($mv_cpo) ) {
                $qv['meta_query'][] = array(
                    'key' => 'mvcpo_categorie_principale_organisation',
                    'value' => $mv_cpo,
                    'compare' => 'IN'
                );
            }

            $mv_cso = isset( $_GET['mv_cso'] ) ? $_GET['mv_cso'] : '';
            if( !empty( $mv_cso ) && isset($mv_cso) ) {
                $qv['meta_query'][] = array(
                    'key' => 'cso_grp_categorie_secondaire_organisation',
                    'value' => $mv_cso,
                    'compare' => 'IN'
                );
            }

            $mv_cto = isset( $_GET['mv_cto'] ) ? $_GET['mv_cto'] : '';
            if( !empty( $mv_cto ) && isset($mv_cto) ) {
                $qv['meta_query'][] = array(
                    'key' => 'cto_grp_categorie_tertiaire_organisation',
                    'value' => $mv_cto,
                    'compare' => 'IN'
                );
            }


            $mv_cpg = isset( $_GET['mv_cpg'] ) ? $_GET['mv_cpg'] : '';
            if( !empty( $mv_cpg ) && isset($mv_cpg) ) {
                $qv['meta_query'][] = array(
                    'key'		=> 'cp_values',
                    'value'		=> $mv_cpg,
                    'compare'	=> 'IN',

                );
            }



            $mv_label = isset( $_GET['mv_label'] ) ? $_GET['mv_label'] : '';

            if( !empty( $mv_label ) && isset($mv_label) ) {
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



function convert_acf_multiselect_to_standard_wp($post_id) {

    if (get_post_type() != 'mv_address_book') {
        return;
    }
        // use a different field name for your converted value
        $cp_meta_key = 'cp_values';
        $label_meta_key = 'label_values';
        // clear any previously stored values
        delete_post_meta($post_id, $cp_meta_key);
        delete_post_meta($post_id, $label_meta_key);
        // get new acf value

        $cp_group = get_field('categories_produits_grp', $post_id);
        $cp_values = $cp_group['categories_produits'];


        if (is_array($cp_values) && count($cp_values)) {
            foreach ($cp_values as $value) {
                add_post_meta($post_id, $cp_meta_key, $value, false);
            }
        }

        $label_group = get_field('label_grp', $post_id);
        $label_values = $label_group['label'];

        if (is_array($label_values) && count($label_values)) {
            foreach ($label_values as $value) {
                add_post_meta($post_id, $label_meta_key, $value, false);
            }
        } // end if

}
add_filter('acf/save_post', 'convert_acf_multiselect_to_standard_wp', 20);

function cptui_register_my_taxes_nomenclature_beta() {

    /**
     * Taxonomy: Beta Nomenclature items.
     */

    $labels = array(
        "name" => __( "Beta Nomenclature items", "mooveat" ),
        "singular_name" => __( "Beta Nomenclature item", "mooveat" ),
    );

    $args = array(
        "label" => __( "Beta Nomenclature items", "mooveat" ),
        "labels" => $labels,
        "public" => true,
        "hierarchical" => true,
        "label" => "Beta Nomenclature items",
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => array( 'slug' => 'nomenclature_beta', 'with_front' => true,  'hierarchical' => true, ),
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "nomenclature_beta",
        "show_in_quick_edit" => false,
    );
    register_taxonomy( "nomenclature_beta", array( "cpt_mooveat_client" ), $args );
}

add_action( 'init', 'cptui_register_my_taxes_nomenclature_beta' );




