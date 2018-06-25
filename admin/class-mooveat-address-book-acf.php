<?php

class Mooveat_Address_Book_ACF
{
    private $version;

    public function __construct($version)
    {

        $this->version = $version;

        add_action('plugins_loaded', array( $this, 'acf_options_page') );

        add_filter( 'terms_clauses', array( $this, 'terms_clauses_multiple_parents'), 20, 3 );

        // Populate select field using filter
        add_filter('acf/load_field/name=categorie_principale_organisation', array( $this, 'acf_load_category_principal') );

        // Populate select field using filter
        add_filter('acf/load_field/name=categorie_secondaire_organisation', array( $this, 'acf_load_category_secondary') );


        // Populate select field using filter
        add_filter('acf/load_field/name=categorie_tertiaire_organisation', array( $this, 'acf_load_category_third') );

        // Populate select field using filter
        add_filter('acf/load_field/name=categories_produits', array( $this, 'acf_load_categories_produits') );

        // Populate select field u
        add_filter('acf/load_field/name=label', array( $this, 'acf_load_labels') );

        add_filter('ac/column/value', array( $this, 'ac_cpo_column_value'), 20, 3 );

//        add_action( 'save_post', array( $this, 'save_post' ) );

        add_action( 'admin_notices', array($this, 'admin_notice'), 20 );

//        add_action('init', array($this, 'admin_only') );

        add_action( 'delete_user', array($this, 'update_contact_from_WP_USER'),20, 1 );

    }



// Add ACF options page for import/export
    public function acf_options_page()
    {
        if( function_exists('acf_add_options_page') ) {

            acf_add_options_page(array(
                'page_title' 	=> 'Import / Export',
                'menu_title'	=> 'Import / Export',
                'menu_slug' 	=> 'import-export-addressbook',
                'capability'	=> 'edit_posts',
                'parent_slug'   => 'edit.php?post_type=mv_address_book',
                'redirect'		=> false
            ));

        }
    }


    // Parent can be array with multiple terms
    public function terms_clauses_multiple_parents( $pieces, $taxonomies, $args )
    {
        // Bail if we are not currently handling our specified taxonomy
        if (!in_array('nomenclature_beta', $taxonomies))
            return $pieces;

        // Check if our custom argument, 'wpse_parents' is set, if not, bail
        if (!isset ($args['wpse_parents'])
            || !is_array($args['wpse_parents'])
        )
            return $pieces;

        // If  'wpse_parents' is set, make sure that 'parent' and 'child_of' is not set
        if ($args['parent']
            || $args['child_of']
        )
            return $pieces;

        // Validate the array as an array of integers
        $parents = array_map('intval', $args['wpse_parents']);

        // Loop through $parents and set the WHERE clause accordingly
        $where = [];
        foreach ($parents as $parent) {
            // Make sure $parent is not 0, if so, skip and continue
            if (0 === $parent)
                continue;

            $where[] = " tt.parent = '$parent'";
        }

        if (!$where)
            return $pieces;

        $where_string = implode(' OR ', $where);
        $pieces['where'] .= " AND ( $where_string ) ";

        return $pieces;
    }


    public function my_post_title_updater($post_id)
    {

        if ((wp_is_post_revision($post_id) || wp_is_post_autosave($post_id))) {
            return;
        }

          $my_contact = array(
            'ID' => $post_id,
            'post_status' => 'publish',
            'post_name' => $post_id,
            'post_type' => 'mv_address_book'
        );


        $contact_group = get_field('contact', $post_id);

        $firstName = $contact_group['first_name'];
        $nom = $contact_group['nom'];
        $fullName = $firstName . ' ' . $nom;

        $my_contact['post_title'] = $fullName;

        if (get_post_type() == 'mv_address_book') {
            // Update the post into the database

            wp_insert_post($my_contact);

        }


    }

    public function save_post_query_var( ) {
        add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );

    }

    public function add_notice_query_var( $location ) {
        remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
        return add_query_arg( array( 'saved' => 'true' ), $location );
    }





    public function admin_notice() {

        global $pagenow;

        if ( $pagenow == 'user-edit.php' ) {

            $user_id = isset ($_GET['user_id']) ? $_GET['user_id'] : '';
            $is_linked_ID = get_user_meta($user_id, 'user_linked_to_contact', true);


            if ($is_linked_ID) {
                $contact_url = get_edit_post_link( $is_linked_ID );
                $contact_name = get_the_title($is_linked_ID);

                if ($contact_name){

                    $class = 'notice notice-info is-dismissible';
                    $message = sprintf(__('This user belongs to contact entry ', 'mv') . '<a href="%1$s">%2$s</a>', $contact_url, $contact_name);

                    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message  );
                }

            }

        }


        if ( ! isset( $_GET['saved'] ) ) {
            return;
        }

        if ( $this->is_empty_email() == true && $this->checked_wpuser() == true ){

            $class = 'notice notice-error is-dismissible';
            $message = __( 'Please input email address to create WP-USER', 'mv' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }


        if( $this->is_email_duplicate() == true  && $this->checked_wpuser() == true && $this->is_empty_email() == false ){

            $class = 'notice notice-error is-dismissible';
            $message = __( 'Duplicate email found', 'mv' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }


    }

    public function is_empty_email() {
        $email = get_post_meta( get_the_ID(), 'social_email', true );

        if ( !($email) ) {
            // email is empty
            return true;
        } else {
            return false;
        }

    }

    public function checked_wpuser() {
        $wp_user_checked = get_post_meta( get_the_ID(), 'contact_wp_user', true );

        if( !$wp_user_checked ){
            //checkbox is not checked
           return false;
        } else {
            return true;
        }
    }

    public function is_email_duplicate() {
        $email_exist = get_post_meta( get_the_ID(), 'duplicate_email_exist', true );

        if ( ($email_exist == 1)  ){
            //duplicate email found
            return true;
        } else {
            return false;
        }
    }

    function create_wpuser_from_contact($post_id) {

        $email_key = 'duplicate_email_exist';
        $email = get_post_meta( get_the_ID(), 'social_email', true );

        delete_post_meta($post_id, $email_key);

        if (!$email) {
            return;
        }

        $contacts = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => 'mv_address_book',
            'exclude'   => $post_id,
            'meta_key' => 'social_email',
            'meta_value' => $email,
        ));

        $contact_url = get_edit_post_link( $post_id );
        $contact_name = get_the_title($post_id);


        if( !empty($contacts) ) {
            // some contact found
            add_post_meta($post_id, $email_key, 1, false);

            // If this is a revision, don't send the email.
            if ( wp_is_post_revision( $post_id ) )
                return;

            $subject = 'A contact has duplicate email';

            $message = "L’email de cette personne est déjà présent dans la liste des utilisateurs wordpress\n\n";
            $message .= '<a href="'. $contact_url . '">' . $contact_name . '</a>';

            // Send email to admin.
            if ($this->is_email_duplicate() == true  && $this->checked_wpuser() == true && $this->is_empty_email() == false){

                $admin_email = get_option( 'admin_email' );
                wp_mail( $admin_email, $subject, $message );
            }



        } else {

            $existed_user_ID = email_exists($email);

            // bail early if  wp-user checkbox is not checked
            if ( $this->checked_wpuser() == false ) {
                // delete user meta about link with contact entry
                delete_user_meta( $existed_user_ID, 'user_linked_to_contact', $post_id);
                return;
            }

            // bail early if email is empty
            if ( $this->is_empty_email() == true ) {
                return;
            }

            // bail early if duplicate  email found in another contact entry
            if ( $this->is_email_duplicate() == true ){
                return;
            }



                // check if existing user found and update its information
                if ( $existed_user_ID ) {
                    $arg = array(
                        'ID' => $post_id,
                        'post_author' => $existed_user_ID,
                    );
                    //link contact with WP-USER
                    wp_update_post($arg);

                    // update WP-USER from contact data

                    $userdata = array(
                        'ID'        => $existed_user_ID,
                        'display_name' => $contact_name,
                        'user_pass' => NULL  // When creating an user, `user_pass` is expected.
                    );
                    wp_update_user( $userdata );

                    // add user meta about link with contact entry
                    add_user_meta( $existed_user_ID, 'user_linked_to_contact', $post_id);

                } else {

                    // If not existing user found then create a new user
                    $userdata = array(
                        'user_login' => $email,
                        'user_email' => $email,
                        'display_name' => $contact_name,
                        'user_pass' => NULL  // When creating an user, `user_pass` is expected.
                    );

                    $new_user_id = wp_insert_user($userdata);

                    // add user meta about link with contact entry
                    add_user_meta( $new_user_id, 'user_linked_to_contact', $post_id);

                    if ( $new_user_id ) {
                        $arg = array(
                            'ID' => $post_id,
                            'post_author' => $new_user_id,
                        );
                        wp_update_post($arg);
                    }

                }


        }



    }


    public function update_contact_from_WP_USER( $user_id ) {

        $is_linked_ID = get_user_meta($user_id, 'user_linked_to_contact', true);

        // check if its corresponding user
        if ($is_linked_ID) {

            // Un-check WP-USER in corresponding contact entry
            update_post_meta($is_linked_ID, 'contact_wp_user', null);

        }
    }

    public function acf_load_category_principal($field)
    {

        // Reset choices
        $field['choices'] = array();

        /**
         * Get all direct child's of specific parent terms. Note we use 'wpse_parents' => id to only get terms for
         *
         * @see get_terms
         * @link http://codex.wordpress.org/Function_Reference/get_terms
         */

        $terms = get_terms(array(
            'taxonomy' => 'nomenclature_beta',
            'orderby' => 'name',
            'order' => 'ASC',
            'wpse_parents' => [772, 766],
            'hide_empty' => false

        ));


        // Populate
        $field['choices'][''] = 'Select Category';

        if ($terms) {
            foreach ($terms as $term) {
                $field['choices'][$term->term_id] = $term->name;
            }
        }

        // Return values
        return $field;

    }

    public function acf_load_category_secondary($field)
    {


//        $cso_group = get_field('contact');
//        $selected_cso = $cso_group['wp_user'];
//
//        var_dump($selected_cso);

//        $key = get_post_meta( get_the_ID(), 'mvcpo_categorie_principale_organisation', true );
//        $key = get_post_meta( get_the_ID(), 'contact_wp_user', true );
//        var_dump($key);

        // Reset choices
        $field['choices'] = array();

        $cpo_group = get_field('mvcpo');
        $selected_cpo = "";
        if ( isset( $cpo_group['categorie_principale_organisation']['value'] ) ) {
            $selected_cpo = $cpo_group['categorie_principale_organisation']['value'];
        }


        // Populate
//        $field['choices'][''] = 'Select Category';

        if ( !empty( $selected_cpo ) ) {

            $terms = get_terms(array(
                'taxonomy' => 'nomenclature_beta',
                'orderby' => 'name',
                'order' => 'ASC',
                'parent' => $selected_cpo,
                'hide_empty' => false

            ));

            if ($terms) {
                foreach ($terms as $term) {
                    $field['choices'][$term->term_id] = $term->name;
                }
            }

        }

        // Return values
        return $field;

    }


    // AJAX Returns category secondary organization by category principal organization
    public function secondary_by_principal_category()
    {

        // Verify nonce AJAX
        if (!isset($_POST['cpo_nonce']) || !wp_verify_nonce($_POST['cpo_nonce'], 'cpo_nonce'))
            die('Permission denied');

        // Get principal selected var
        $selected_cpo = $_POST['selected_cpo'];

        $terms = get_terms(array(
            'taxonomy' => 'nomenclature_beta',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => $selected_cpo,
            'hide_empty' => false

        ));

        foreach ($terms as $term) {

            $new_cso[] = array(
                $term->term_id => $term->name
            );

        }

        // Returns direct child terms as array if there is selected category principal organization
        if ($selected_cpo) {

            return wp_send_json($new_cso);

        } else {

            return wp_send_json(null);

        }

        die();
    }

    //load value on page load
    public function acf_load_category_third($field)
    {


        // Reset choices
        $field['choices'] = array();

        $cso_group = get_field('cso_grp');
        $selected_cso = "";
        if ( isset( $cso_group['categorie_secondaire_organisation']['value'] ) ) {
            $selected_cso = $cso_group['categorie_secondaire_organisation']['value'];
        }

        // Populate
//        $field['choices'][''] = 'Select Category';

        if ( !empty( $selected_cso ) ) {

            $terms = get_terms(array(
                'taxonomy' => 'nomenclature_beta',
                'orderby' => 'name',
                'order' => 'ASC',
                'parent' => $selected_cso,
                'hide_empty' => false

            ));

            if ($terms) {
                foreach ($terms as $term) {
                    $field['choices'][$term->name] = $term->name;
                }
            }

        }

        // Return values
        return $field;

    }


    // AJAX Returns category third organization by category secondary organization
    public function third_by_secondary_category()
    {

        // Verify nonce AJAX
        if (!isset($_POST['cpo_nonce']) || !wp_verify_nonce($_POST['cpo_nonce'], 'cpo_nonce'))
            die('Permission denied');

        // Get principal selected var
        $selected_cso = $_POST['selected_cso'];

        $terms = get_terms(array(
            'taxonomy' => 'nomenclature_beta',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => $selected_cso,
            'hide_empty' => false

        ));

        foreach ($terms as $term) {

            $new_cto[] = array(
                $term->name => $term->name
            );

        }

        // Returns direct child terms as array if there is selected category principal organization
        if ($selected_cso) {

            return wp_send_json($new_cto);

        } else {

            return wp_send_json(null);

        }

        die();
    }


    public function acf_load_categories_produits($field)
    {

        // Reset choices
        $field['choices'] = array();

        /**
         * Get all direct child's of specific parent terms. Note we use 'wpse_parents' => id to only get terms for
         *
         * @see get_terms
         * @link http://codex.wordpress.org/Function_Reference/get_terms
         */

        $terms = get_terms(array(
            'taxonomy' => 'categorie_producteur_point_vente',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => '671',
            'hide_empty' => false

        ));


        // Populate
//        $field['choices'][''] = 'Select Category';

        foreach ($terms as $term) {
            $field['choices'][$term->name] = $term->name;
        }

        // Return values
        return $field;

    }



    public function acf_load_labels($field)
    {

        // Reset choices
        $field['choices'] = array();

        /**
         * Get all direct child's of specific parent terms. Note we use 'wpse_parents' => id to only get terms for
         *
         * @see get_terms
         * @link http://codex.wordpress.org/Function_Reference/get_terms
         */

        $terms = get_terms(array(
            'taxonomy' => 'categorie_producteur_point_vente',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => '56',
            'hide_empty' => false

        ));


        // Populate
//        $field['choices'][''] = 'Select Label';

        foreach ($terms as $term) {
            $field['choices'][$term->name] = $term->name;
        }

        // Return values
        return $field;

    }


    function ac_cpo_column_value( $value, $id, $column ) {
        if ( $column instanceof ACP_Column_CustomField ) {
            $meta_key = $column->get_meta_key(); // This gets the Custom Field key

            if ( 'mvcpo_categorie_principale_organisation' == $meta_key ) {

                if ($value != '&ndash;') {

                    $cpo_group = get_field_object('mvcpo');
                    $selected_cpo = $cpo_group['sub_fields'][0];
                    $selected_cpo_label = $selected_cpo['choices'][ $value ];

                    $value = $selected_cpo_label;

                }
            }

            if ( 'cso_grp_categorie_secondaire_organisation' == $meta_key ) {

                if ($value != '&ndash;') {

                    $cpo_group = get_field_object('cso_grp');
                    $selected_cpo = $cpo_group['sub_fields'][0];
                    $selected_cpo_label = $selected_cpo['choices'][ $value ];

                    $value = $selected_cpo_label;

                }
            }


//            if ( 'cto_grp_categorie_tertiaire_organisation' == $meta_key ) {
//
//                if ($value != '&ndash;') {
//
//                    $cpo_group = get_field_object('cto_grp');
//                    $selected_cpo = $cpo_group['sub_fields'][0];
//                    $selected_cpo_label = $selected_cpo['choices'][ $value ];
//
//                    $value = $selected_cpo_label;
//
//                }
//            }


//            if ( 'social_email' == $meta_key ) {
//
//                    echo ( 'jimi007' );
//
//
//            }

        }

        return $value;
    }

}