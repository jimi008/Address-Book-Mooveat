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


require_once plugin_dir_path( __FILE__ ) . 'includes/class-mooveat-address-book.php';

function run_mooveat_address_book() {

    $spmm = new Mooveat_Address_Book();
    $spmm->run();

}

run_mooveat_address_book();
