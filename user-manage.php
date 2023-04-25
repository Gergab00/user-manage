<?php
/**
 * 
 * Plugin Name: User Manager
 * Author: Gerardo Gabriel Gonzalez Velazquez
 * Author URI: https://gerardo-gonzalez.dev/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: 
 * 
 * GitHub Plugin URI: https://github.com/Gergab00/user-manager
 * 
 * @package UserManager
 */

// Define the plugin path and URL.
define( 'USER_MANAGER_PLUGIN_FILE', __FILE__ );
define( 'USER_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'USER_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'USER_MANAGER_PLUGIN_VERSION', '1.0.0' );

function plugin_init() {
    require_once USER_MANAGER_PLUGIN_DIR . '/functions.php';
}

add_action( 'plugins_loaded', 'plugin_init' );