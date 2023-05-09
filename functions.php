<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$includes = array(
    'admin/AdminPages',
    'classes/ChangeOneUser',
    'classes/DatabaseManage'
);

foreach($includes as $file){
    require_once USER_MANAGER_PLUGIN_DIR . $file . '.php';
}

$adminPages = new AdminPAges(DatabaseManage::getAllTables());

if ( is_multisite() ) {
    add_action( 'network_admin_menu' , array( $adminPages, 'adminMenu' ) );
} else {
    add_action( 'admin_menu', array( $adminPages, 'adminMenu' ) );
}

add_action( 'admin_post_update_user_id', DatabaseManage::postUpdateUserID() );
add_action( 'admin_post_update_users_ids', DatabaseManage::postUpdateAllUsersID() );

function update_user_id_admin_init() {
    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update-user-id' ) ) {
        do_action( 'admin_post_update_user_id' );
    }
}
add_action( 'admin_init', 'update_user_id_admin_init' );

// Activate the plugin.
function user_manage_activate() {
    // Do activation tasks here.
    global $wpdb;

    // Check if the users table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}users'") == "{$wpdb->prefix}users") {

        // Disable auto-increment on the ID column
        $wpdb->query("ALTER TABLE {$wpdb->prefix}users MODIFY id bigint(20) unsigned NOT NULL");

    }
}
register_activation_hook( USER_MANAGER_PLUGIN_FILE, 'user_manage_activate' );

// Función para reactivar autoincrement al desactivar el plugin
function reactivate_autoincrement_on_plugin_deactivation() {
    global $wpdb;

    // Ejecutar consulta SQL para reactivar autoincrement en wp_users
    $wpdb->query( "ALTER TABLE wp_users CHANGE ID ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );
}

// Registrar la función para que se ejecute en la desactivación del plugin
register_deactivation_hook( __FILE__, 'reactivate_autoincrement_on_plugin_deactivation' );

