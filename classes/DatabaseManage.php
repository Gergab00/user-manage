<?php

class DatabaseManage {

    public static function getAllTables() {
        global $wpdb;
        $exclude_tables = array('commentmeta', 'comments', 'options', 'postmeta', 'post', 'term', 'links');
        $tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
        $results = array();
    
        foreach ( $tables as $table ) {
            $table_name = $table[0];
            $exclude = false;
            foreach ($exclude_tables as $excluded) {
                if (stripos($table_name, $excluded) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || $table_name === $wpdb->users || $table_name === $wpdb->usermeta) {
                continue; // Saltar a la siguiente tabla si está en la lista de exclusiones o es users o usermeta
            }
            $fields = $wpdb->get_results( "DESCRIBE $table_name", ARRAY_A );
            $results[] = array(
                'table_name' => $table_name,
                'fields'     => $fields
            );
        }
    
        return $results;
    }
    
    
    public static function postUpdateUserID() {
        if ( isset( $_POST['user_id'] ) && isset( $_POST['new_id'] ) ) {
            $old_id = intval( $_POST['user_id'] );
            $new_id = intval( $_POST['new_id'] );
    
            // Actualizar el ID del usuario
            self::modifyUserId( $old_id, $new_id );
            self::getTablesToUpdate( $old_id, $new_id );
    
            // Redirigir a la página anterior
            wp_redirect( esc_url_raw( $_POST['_wp_http_referer'] ) );
            exit;
        }
    }

    protected static function modifyUserId( $old_id, $new_id ) {
        global $wpdb;
        $user_table = $wpdb->prefix . 'users';
        $usermeta_table = $wpdb->prefix . 'usermeta';
     
        // Modify user ID in wp_users table
        $wpdb->update( $user_table, array( 'ID' => $new_id ), array( 'ID' => $old_id ) );
     
        // Modify user ID in wp_usermeta table
        $wpdb->update( $usermeta_table, array( 'user_id' => $new_id ), array( 'user_id' => $old_id ) );

        // Modify user ID in wp_posts table
        $wpdb->update( $wpdb->posts, array( 'post_author' => $new_id ), array( 'post_author' => $old_id ) );

        // Modify user ID in wp_comments table
        $wpdb->update( $wpdb->comments, array( 'user_id' => $new_id ), array( 'user_id' => $old_id ) );
    }

    public static function getAllUsers(){
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM $wpdb->users" );
    }

    public static function getTablesToUpdate( $old_id, $new_id ) {
        global $wpdb;
        $post_data = $_POST;
    
        foreach ( $post_data as $table => $field ) {
            $showTable = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if (!empty($showTable) && $field != 'none') {
                self::updateUserID( $old_id, $new_id, $table, $field );
            }
        }
    }
    

    protected static function updateUserID( $old_id, $new_id, $table, $field ){
        global $wpdb;
        $wpdb->update( $table, array( $field => $new_id ), array( $field => $old_id ) );
    }
    
}