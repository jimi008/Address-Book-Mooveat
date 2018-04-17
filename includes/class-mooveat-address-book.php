<?php

class Mooveat_Address_Book {

    protected $loader;

    protected $plugin_slug;

    protected $version;

    public function __construct() {

        $this->plugin_slug = 'mooveat-address-book';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();

    }

    private function load_dependencies() {

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mooveat-address-book-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mooveat-address-book-acf.php';

        require_once plugin_dir_path( __FILE__ ) . 'class-mooveat-address-book-loader.php';
        $this->loader = new Mooveat_Address_Book_Loader();


    }

    private function define_admin_hooks() {

        $admin = new Mooveat_Address_Book_Admin( $this->get_version() );
        $acf = new Mooveat_Address_Book_ACF( $this->get_version() );

        $this->loader->add_action('admin_enqueue_scripts', $admin, 'admin_scripts');
        $this->loader->add_action( 'admin_init' , $admin, 'remove_columns_init', 20 );
        //ajax call
        $this->loader->add_action('wp_ajax_cso_category', $acf, 'secondary_by_principal_category');
        $this->loader->add_action('wp_ajax_nopriv_cso_category', $acf , 'secondary_by_principal_category');
        //save acf contact as post title - fire on new post
        $this->loader->add_action( 'save_post_mv_address_book', $admin, 'mv_contact_name_as_post_title', 20, 3);
        // save acf contact as post title on update post
        $this->loader->add_action('acf/save_post', $acf, 'my_post_title_updater', 20, 2);

    }

    public function run() {
        $this->loader->run();
    }

    public function get_version() {
        return $this->version;
    }

}