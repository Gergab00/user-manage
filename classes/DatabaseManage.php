<?php

/*TODO - 
DatabaseTables: una clase para obtener todas las tablas de la base de datos y excluir las que no se necesitan.
DatabaseUser: una clase para actualizar los IDs de usuario y fusionar usuarios.
DatabaseQuery: una clase para ejecutar consultas SQL en la base de datos.
*/

class DatabaseManage
{

    public static function getAllTables()
    {
        global $wpdb;
        $exclude_tables = array('commentmeta', 'comments', 'options', 'postmeta', 'post', 'term', 'links');
        $tables         = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        $results        = array();

        foreach ($tables as $table) {
            $table_name = $table[0];
            $exclude    = false;
            foreach ($exclude_tables as $excluded) {
                if (stripos($table_name, $excluded) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || $table_name === $wpdb->users || $table_name === $wpdb->usermeta) {
                continue; // Saltar a la siguiente tabla si está en la lista de exclusiones o es users o usermeta
            }
            $fields    = $wpdb->get_results("DESCRIBE $table_name", ARRAY_A);
            $results[] = array(
                'table_name' => $table_name,
                'fields'     => $fields,
            );
        }

        return $results;
    }

    public static function postUpdateUserID()
    {
        if (isset($_POST['user_id']) && isset($_POST['new_id'])) {
            $old_id = intval($_POST['user_id']);
            $new_id = intval($_POST['new_id']);

            // Actualizar el ID del usuario
            self::modifyUserId($old_id, $new_id);
            self::getTablesToUpdate($old_id, $new_id, $_POST);

            // Redirigir a la página anterior
            wp_redirect(esc_url_raw($_POST['_wp_http_referer']));
            exit;
        }
    }

/**
 * Actualiza los ID de todos los usuarios dentro del rango especificado.
 *
 * @return void
 */
    public static function postUpdateAllUsersID()
    {
        // Verificar que se reciben los parámetros necesarios por POST
        if (isset($_POST['start_count_ids']) && isset($_POST['range_start']) && isset($_POST['range_end'])) {
            // Convertir los parámetros a enteros
            $strartIDS  = intval($_POST['start_count_ids']);
            $rangeStart = intval($_POST['range_start']);
            $rangeEnd   = intval($_POST['range_end']);
            $site       = intval($_POST['site']);
            $site       = (1 == $site) ? '' : $site;

            // Obtener los usuarios dentro del rango especificado
            $users = self::getUsersInRange($rangeStart, $rangeEnd);

            // Recorrer cada usuario y actualizar su ID
            foreach ($users as $user) {
                $old_id = intval($user->ID);
                $new_id = $strartIDS++;
                // Actualizar el ID del usuario
                self::modifyUserId($old_id, $new_id, $site);

                self::updateUserAvatarPostmeta($new_id, $site);

                // Obtener las tablas a actualizar para este usuario
                // y agregar las consultas SQL necesarias a la cola
                self::getTablesToUpdate($old_id, $new_id, $_POST);

            }
        }
    }

    public static function mergeUsers(){
        if (isset($_POST['action']) && 'manage_duplicate_user' == $_POST['action']){
            $users = self::obtenerUsuariosDuplicados();
            $tables = self::getTablesWithUserColumn();

            foreach ($users as $user) {
                $user = get_object_vars($user);
                // Accedemos al valor del elemento "user_email"
                $user_email = $user["user_email"];
                // Accedemos al valor del elemento "ids"
                $ids_array = explode(",", $user["ids"]); // Convertir la cadena de ids en un array
                $new_id = array_shift($ids_array);

                foreach($ids_array as $old_id){
                    self::modifyUserId($old_id, $new_id);

                    self::updateUserAvatarPostmeta($new_id);
    
                    // Obtener las tablas a actualizar para este usuario
                    // y agregar las consultas SQL necesarias a la cola
                    self::getTablesToUpdate($old_id, $new_id, $tables);
                }

            }
        }
    }

    protected static function getTablesWithUserColumn() {
        global $wpdb; // Necesitamos acceder a la instancia global de la base de datos de WordPress
        
        $tables = array();
        
        // Consulta para obtener todas las tablas que tengan una columna que contenga "user" en su nombre
        $results = $wpdb->get_results("SELECT DISTINCT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE COLUMN_NAME LIKE '%user%'");
        
        // Almacenar los nombres de tabla en un array
        foreach ($results as $result) {
            $tables[$result->TABLE_NAME] = $result->COLUMN_NAME;
        }
        
        return $tables;

    }
    
    protected static function modifyUserId($old_id, $new_id, $site = '')
    {
        global $wpdb;
        if (!empty($site)) {
            $site = $site . '_';
        }
        $user_table     = $wpdb->prefix . 'users';
        $usermeta_table = $wpdb->prefix . 'usermeta';
        $posts_table    = $wpdb->prefix . $site . 'posts';
        $comments_table = $wpdb->prefix . $site . 'comments';

        $wpdb->query('START TRANSACTION');

        try {
            // Modify user ID in wp_users table
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $user_table SET ID = %d WHERE ID = %d",
                    $new_id,
                    $old_id
                )
            );

            // Modify user ID in wp_usermeta table
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $usermeta_table SET user_id = %d WHERE user_id = %d",
                    $new_id,
                    $old_id
                )
            );

            // Modify user ID in wp_posts table
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $posts_table SET post_author = %d WHERE post_author = %d",
                    $new_id,
                    $old_id
                )
            );

            // Modify user ID in wp_comments table
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $comments_table SET user_id = %d WHERE user_id = %d",
                    $new_id,
                    $old_id
                )
            );

            $wpdb->query('COMMIT');

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    public static function getAllUsers()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $wpdb->users");
    }

    public static function getUsersInRange($min_id, $max_id)
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * FROM $wpdb->users WHERE ID BETWEEN %d AND %d",
            $min_id,
            $max_id
        );
        return $wpdb->get_results($query);
    }

    protected static function getTablesToUpdate($old_id, $new_id, $post_data)
    {
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');

            foreach ($post_data as $table => $field) {
                $showTable = $wpdb->get_var("SHOW TABLES LIKE '$table'");
                if (!empty($showTable) && 'none' != $field) {
                    self::updateUserID($old_id, $new_id, $table, $field, $wpdb);
                }
            }

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    protected static function updateUserID($old_id, $new_id, $table, $field)
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "UPDATE $table SET $field = %d WHERE $field = %d",
            $new_id,
            $old_id
        );
        $wpdb->query($query);
    }

    protected static function updateUserAvatarPostmeta($new_id, $site = '')
    {
        global $wpdb;
        if (!empty($site)) {
            $site = $site . '_';
        }

        $usermeta_table = $wpdb->prefix . 'usermeta';
        $postmeta_table = $wpdb->prefix . $site . 'postmeta';
        $meta_key       = $wpdb->prefix . $site . '_user_avatar';

        $usermeta_query = $wpdb->prepare(
            "SELECT meta_value FROM $usermeta_table WHERE meta_key LIKE %s",
            '%' . $meta_key . '%'
        );
        $usermeta_results = $wpdb->get_results($usermeta_query);

        foreach ($usermeta_results as $usermeta_row) {
            $postmeta_query = $wpdb->prepare(
                "UPDATE $postmeta_table SET meta_value = %d WHERE post_id IN (SELECT meta_value FROM $usermeta_table WHERE meta_key = %s AND meta_value = %d) AND meta_key = '_wp_attachment_wp_user_avatar'",
                $new_id,
                $meta_key,
                $usermeta_row->meta_value
            );
            $wpdb->query($postmeta_query);
        }
    }

    protected static function obtenerUsuariosDuplicados()
    {
        global $wpdb;
        $user_table     = $wpdb->prefix . 'users';
        $query = "SELECT `user_email`, GROUP_CONCAT(id) AS ids FROM ".$user_table." GROUP BY `user_email` HAVING COUNT(*) > 1";
        $usuarios_duplicados = $wpdb->get_results($query);
        return $usuarios_duplicados;
    }

}
/*

ALTER TABLE oxh_users
ADD COLUMN spam INT(11) DEFAULT 0 AFTER display_name,
ADD COLUMN deleted INT(11) DEFAULT 0 AFTER spam;
INSERT INTO mhi_users SELECT * FROM oxh_users;
INSERT INTO mhi_usermeta (user_id, meta_key, meta_value)
SELECT user_id, meta_key, meta_value FROM oxh_usermeta;

UPDATE mhi_usermeta SET meta_key = 'mhi_capabilities' WHERE meta_key = 'oxh_capabilities';
UPDATE mhi_usermeta SET meta_key = 'mhi_user_level' WHERE meta_key = 'oxh_user_level';
UPDATE mhi_usermeta SET meta_key = 'mhi_dashboard_quick_press_last_post_id' WHERE meta_key = 'oxh_dashboard_quick_press_last_post_id';
UPDATE mhi_usermeta SET meta_key = 'mhi_user-settings' WHERE meta_key = 'oxh_user-settings';
UPDATE mhi_usermeta SET meta_key = 'mhi_user-settings-time' WHERE meta_key = 'oxh_user-settings-time';
UPDATE mhi_usermeta SET meta_key = 'mhi_ac_preferences_settings' WHERE meta_key = 'oxh_ac_preferences_settings';
UPDATE mhi_usermeta SET meta_key = 'mhi_ac_preferences_layout_table' WHERE meta_key = 'oxh_ac_preferences_layout_table';
UPDATE mhi_usermeta SET meta_key = 'mhi_user_avatar' WHERE meta_key = 'oxh_user_avatar';
UPDATE mhi_usermeta SET meta_key = 'mhi_media_library_mode' WHERE meta_key = 'oxh_media_library_mode';
UPDATE mhi_usermeta SET meta_key = 'mhi_elementor_connect_common_data' WHERE meta_key = 'oxh_elementor_connect_common_data';

UPDATE mhi_2_postmeta pm
SET pm.meta_value = (
SELECT um.user_id
FROM mhi_usermeta um
WHERE um.meta_key = 'mhi_2_user_avatar'
AND um.meta_value = pm.post_id
)
WHERE pm.meta_key = '_wp_attachment_wp_user_avatar';

 */
