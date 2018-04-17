<?php

class Mooveat_Address_Book_Admin
{

    private $version;

    public function __construct($version)
    {
        $this->version = $version;

        add_action( 'do_meta_boxes' , array( $this, 'remove_meta_boxes' ));
        add_filter( 'screen_layout_columns', array( $this, 'two_screen_layout_columns' ));
        add_filter( 'get_user_option_screen_layout_mv_address_book', array( $this, 'two_screen_layout_post' ));
        add_filter('post_row_actions', array( $this, 'mv_hide_quick_edit'), 10, 2);
        add_action('add_meta_boxes', array( $this,  'mv_remove_wp_seo_meta_box'), 100);
        add_action( 'admin_menu', array( $this, 'mv_address_book_replace_submit_meta_box' ));
        add_filter( 'post_updated_messages', array( $this,  'mv_address_book_cpt_messages' ));
        add_action( 'current_screen', array( $this, 'wpse151723_remove_yoast_seo_posts_filter'), 20 );
//        add_action( 'current_screen', 'wpse151723_remove_yoast_seo_posts_filter', 20 );

    }

    /**
     * Admin styles & Scripts
     *
     * @return void
     */
    public function admin_scripts($hook)
    {

        if ($hook != 'post-new.php' && $hook != 'edit.php' && $hook != 'post.php') {
            return;
        }

        $screen = get_current_screen();

        if ('mv_address_book' === $screen->post_type) {

            wp_enqueue_script('mvab-admin-script', plugin_dir_url( __FILE__ ) . "js/mvab-admin.js", array('jquery'), $this->version);
            wp_enqueue_style('mvab-admin-style', plugin_dir_url( __FILE__ ) . "css/mvab-admin.css", array(), $this->version);

            wp_dequeue_script('autosave');
            wp_deregister_script('postbox');

        }

        //For calling ajax to populate categories select field
        wp_localize_script('mvab-admin-script', 'cpo_vars', array(
            'cpo_nonce' => wp_create_nonce('cpo_nonce'), // Create nonce which we later will use to verify AJAX request
        ));

    }


    //	remove following meta boxes from competition post_type
    public function remove_meta_boxes()
    {
        remove_meta_box('wpseo_meta', 'mv_address_book', 'normal');
    }

    public function two_screen_layout_columns($columns)
    {
        $columns['post'] = 2;
        return $columns;
    }

    public function two_screen_layout_post()
    {
        return 2;
    }


    /**
     * Sets the post status to published
     */
    function mv_contact_name_as_post_title( $post_id )
    {

        if (!(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id))) {
            return;
        }

        $contact_group = get_field('contact', $post_id);

        $firstName = $contact_group['first_name'];
        $nom = $contact_group['nom'];
        $fullName = $firstName . ' ' . $nom;
//    $slug = sanitize_title( $fullName );

        $content = array(
            'ID' => $post_id,
            'post_title' => $fullName,
            'post_name' => $post_id,
            'post_status' => 'publish'
        );

        remove_action('save_post_mv_address_book', array( $this, 'mv_contact_name_as_post_title') );
        wp_update_post($content);
        add_action('save_post_mv_address_book', array( $this, 'mv_contact_name_as_post_title') );
    }


    /**
     * Hide quick edit
     * @internal  Used as a callback.
     * @see  https://developer.wordpress.org/reference/hooks/post_row_actions/
     */

    public function mv_hide_quick_edit($actions, $post)
    {

        if ('mv_address_book' === $post->post_type) {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }



    public function mv_remove_wp_seo_meta_box()
    {
        remove_meta_box('wpseo_meta', 'mv_address_book', 'normal');
    }


    function wpse151723_remove_yoast_seo_posts_filter() {

        $screen = get_current_screen();

        global $wpseo_meta_columns;

        if ('mv_address_book' === $screen->post_type) {

            if ($wpseo_meta_columns) {
                remove_action('restrict_manage_posts', array($wpseo_meta_columns, 'posts_filter_dropdown'));
                remove_action('restrict_manage_posts', array($wpseo_meta_columns, 'posts_filter_dropdown_readability'));

            }

            add_filter('months_dropdown_results', '__return_empty_array');
        }
    }

    public function my_manage_columns($columns)
    {
        unset($columns['wpseo-score']);
        unset($columns['wpseo-score-readability']);
        unset($columns['wpseo-title']);
        unset($columns['wpseo-metadesc']);
        unset($columns['wpseo-focuskw']);
        unset($columns['wpseo-links']);
        unset($columns['wpseo-linked']);
        return $columns;
    }

    public function remove_columns_init()
    {
        add_filter('manage_mv_address_book_posts_columns', array( $this, 'my_manage_columns'), 20, 2);

    }



    /**
     * Loop throught custom post types and
     * replace default submit box
     *
     * @since  1.0
     *
     */
    function mv_address_book_replace_submit_meta_box()
    {
        // create a multidimensional array that will hold
        // each custom post_type as a key, and custom
        // post_type name will be it's value.
        $items = array(
            'mv_address_book' => 'Contact'

        );

        // now loop through $items array and remove, then
        // add submit meta box for each post type, by using
        // values from array to complete this.
        foreach ($items as $item => $value) {
            remove_meta_box('submitdiv', $item, 'core'); // $item represents post_type
            add_meta_box('submitdiv', sprintf(__('Save/Update %s'), $value), array( $this, 'mv_address_book_submit_meta_box'), $item, 'side', 'low'); // $value will be the output title in the box
        }
    }


    /**
     * Custom edit of default wordpress publish box callback
     * loop through each custom post type and remove default
     * submit box, replacing it with custom one that has
     * only submit button with custom text on it (add/update)
     *
     * @global $action , $post
     * @see wordpress/includes/metaboxes.php
     * @since  1.0
     *
     */
    function mv_address_book_submit_meta_box()
    {
        global $action, $post;

        $post_type = $post->post_type; // get current post_type
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        // again, use the same array. It is important
        // to put it in same order, so that it can
        // follow the right meta box
        $items = array(
            'mv_address_book' => 'Address Book'
        );
        // now create var $item that will take only right
        // post_type information for currently displayed
        // post_type. Because $post_type var will store
        // only current post_type, it will correspond to
        // the appropriate 'key' from the $items array.
        // This $item will hold only the string name of
        // the post_type which will be used further in context
        // on appropriate places.
        $item = $items[$post_type];

        echo '<div class="submitbox" id="submitpost">
            <div id="major-publishing-actions">';

                do_action('post_submitbox_start');

                echo '<div id="delete-action">';

                    if (current_user_can("delete_post", $post->ID)) {
                        if (!EMPTY_TRASH_DAYS)
                            $delete_text = __('Delete Permanently');
                        else
                            $delete_text = __('Move to Trash');

                        echo '<a class="submitdelete deletion" href="' . get_delete_post_link($post->ID) . '">'. $delete_text . '</a>';

                    } //if

                echo '</div><div id="publishing-action"><span class="spinner"></span>';

                    if ( !in_array($post->post_status, array('publish', 'future', 'private')) || 0 == $post->ID ) {

                        if ( $can_publish ) {

                            echo '<input name="original_publish" type="hidden" id="original_publish" value="Save Contact"/>';
                            submit_button(sprintf(__('Save Contact %'), $item), 'primary button-large', 'publish', false, array('accesskey' => 'p'));

                        }
                    } else {
                        echo '<input name="original_publish" type="hidden" id="original_publish"
                               value="Save Contact"/>
                        <input name="save" type="submit" class="button button-primary button-large" id="publish"
                               accesskey="p" value="Save Contact"/>';

                    } //if

                echo '</div><div class="clear"></div></div></div>';

    }


    /**
     * mv_address_book CPT updates messages.
     *
     * @param array $messages Existing post update messages.
     *
     * @return array Amended mv_address_book CPT notices
     */
    public function mv_address_book_cpt_messages($messages)
    {
        $post = get_post();
        $post_type = get_post_type($post);
        $post_type_object = get_post_type_object($post_type);

        $messages['mv_address_book'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __('Contact updated.', 'textdomain'),
            2 => __('Custom field updated.', 'textdomain'),
            3 => __('Custom field deleted.', 'textdomain'),
            4 => __('Contact updated.', 'textdomain'),
            5 => isset($_GET['revision']) ? sprintf(__('Contact restored to revision from %s', 'textdomain'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => __('Contact saved.', 'textdomain'),
            7 => __('Contact saved.', 'textdomain'),
            8 => __('Contact submitted.', 'textdomain'),
            9 => sprintf(
                __('Contact scheduled for: <strong>%1$s</strong>.', 'textdomain'),
                date_i18n(__('M j, Y @ G:i', 'textdomain'), strtotime($post->post_date))
            ),
            10 => __('Contact draft updated.', 'textdomain')
        );


        return $messages;
    }





}