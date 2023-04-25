<?php

class AdminPages {

    protected $tables;

    function __construct($tables = array()){
        $this->tables = $tables;
    }

    function adminMenu() {
        add_menu_page(
            'User Manage',
            'Edit All Users',
            'manage_options',
            'user-manage',
            array($this, 'user_manage_admin_page')
        );

        add_submenu_page(
            'user-manage',
            'User Manage',
            'Edit One User',
            'manage_options',
            'edit-one-user',
            array($this, 'edit_one_user_admin_page' )
        );
    }

    function user_manage_admin_page() {
        $tables = $this->tables;
        ob_start();
        include( USER_MANAGER_PLUGIN_DIR . '/admin/views/select-tables.php' );
        echo ob_get_clean();
    }

    function edit_one_user_admin_page() {
        $tables = $this->tables;
        ob_start();
        include( USER_MANAGER_PLUGIN_DIR . '/admin/views/edit-one-user.php' );
        echo ob_get_clean();
    }
}