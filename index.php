<?php

/*
Plugin Name: ACF Customizer
Plugin URI: https://github.com/mcguffin/acf-customizer
Description: Use ACF Fields in customizer.
Author: Jörn Lund
Version: 0.2.10
Tested up to: 5.2
Requires PHP: 5.6
Author URI: https://github.com/mcguffin
License: GPL3
Github Repository: mcguffin/acf-customizer
Github Plugin URI: mcguffin/acf-customizer
Text Domain: acf-customizer
Domain Path: /languages/
*/

/*  Copyright 2018  Jörn Lund

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


namespace ACFCustomizer;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

require_once dirname(__FILE__) . '/include/autoload.php';

Core\Core::instance( __FILE__ );

// github updater overide dot org updates
add_filter( 'github_updater_override_dot_org', function() {
    return [
        'acf-customizer/index.php' //plugin format
    ];
});


if ( is_admin() || defined( 'DOING_AJAX' ) ) {
	// init auto upgrader
	if ( ! file_exists( dirname(__FILE__) . '/.git/' ) ) {

		// Not a git. Check if https://github.com/afragen/github-updater is active
		$active_plugins = get_option('active_plugins');
		if ( $sitewide_plugins = get_site_option('active_sitewide_plugins') ) {
			$active_plugins = array_merge( $active_plugins, array_keys( $sitewide_plugins ) );
		}

		if ( ! in_array( 'github-updater/github-updater.php', $active_plugins ) ) {
			// not github updater. Init our own...
			AutoUpdate\AutoUpdateGithub::instance()->init( __FILE__ );
		}
	}

}

include_once __DIR__ . DIRECTORY_SEPARATOR . 'test/test.php';