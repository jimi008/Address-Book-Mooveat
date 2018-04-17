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
        add_filter('acf/load_field/name=categories_produits', array( $this, 'acf_load_categories_produits') );
        // Populate select field u
        add_filter('acf/load_field/name=label', array( $this, 'acf_load_labels') );

        add_filter('ac/column/value', array( $this, 'ac_cpo_column_value'), 20, 3 );

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
        if (!in_array('categorie_producteur_point_vente', $taxonomies))
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

        $my_post = array();
        $my_post['ID'] = $post_id;

        $contact_group = get_field('contact', $post_id);

        $firstName = $contact_group['first_name'];
        $nom = $contact_group['nom'];
        $fullName = $firstName . ' ' . $nom;

        if (get_post_type() == 'mv_address_book') {
            $my_post['post_title'] = $fullName;
        }

        // Update the post into the database
        wp_update_post($my_post);

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
            'taxonomy' => 'categorie_producteur_point_vente',
            'orderby' => 'name',
            'order' => 'ASC',
            'wpse_parents' => [750, 751],
            'hide_empty' => false

        ));


        // Populate
        $field['choices'][''] = 'Select Category';

        foreach ($terms as $term) {
            $field['choices'][$term->term_id] = $term->name;
        }

        // Return values
        return $field;

    }

    public function acf_load_category_secondary($field)
    {

        // Reset choices
        $field['choices'] = array();

        $cpo_group = get_field('mvcpo');
        $selected_cpo = $cpo_group['categorie_principale_organisation']['value'];

        // Populate
        $field['choices'][''] = 'Select Category';

        if ($selected_cpo) {

            $terms = get_terms(array(
                'taxonomy' => 'categorie_producteur_point_vente',
                'orderby' => 'name',
                'order' => 'ASC',
                'parent' => $selected_cpo,
                'hide_empty' => false

            ));

            foreach ($terms as $term) {
                $field['choices'][$term->name] = $term->name;
            }

        }

        // Return values
        return $field;

    }


    // Returns category secondary organization by category principal organization
    public function secondary_by_principal_category($selected_cpo)
    {

        // Verify nonce
        if (!isset($_POST['cpo_nonce']) || !wp_verify_nonce($_POST['cpo_nonce'], 'cpo_nonce'))
            die('Permission denied');

        // Get principal selected var
        $selected_cpo = $_POST['selected_cpo'];

        $terms = get_terms(array(
            'taxonomy' => 'categorie_producteur_point_vente',
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => $selected_cpo,
            'hide_empty' => false

        ));

        foreach ($terms as $term) {

            $new_cso[] = array(
                $term->name => $term->name
            );

        }

        // Returns direct child terms as array if there is selected category principal organization
        if ($selected_cpo) {

            return wp_send_json($new_cso);

        } else {

            $arr_data = array('No Response');
            return wp_send_json($arr_data);

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

                // Use the color
                if ($value != '&ndash;') {

                    $cpo_group = get_field_object('mvcpo');
                    $selected_cpo = $cpo_group['sub_fields'][0];
                    $selected_cpo_label = $selected_cpo['choices'][ $value ];

                    $value = $selected_cpo_label;

                }
            }
        }

        return $value;
    }

}