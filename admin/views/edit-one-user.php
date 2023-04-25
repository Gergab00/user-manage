<?php
$users = DatabaseManage::getAllUsers();

?>
<div class="wrap">
    <h1>Modificar ID de usuario</h1>
    <form method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                <th scopre="row"><label for="user_id">Seleccionar usuario:</label></th>
                <td>
                <select name="user_id" id="user_id">
                    <?php
                foreach ( $users as $user ) {
                    printf(
                        '<option value="%d">%s</option>',
                        $user->ID,
                        esc_html( $user->user_login )
                    );
                }
                ?>
                </select>
            </td>
            </tr>
            </tbody>
            <br>
            <tbody>
                <th scopre="row"><label for="new_id">Nuevo ID:</label></th>
                <td><input type="text" name="new_id" id="new_id" min="1" required></td>
                </body>
        </table>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>Tabla</th>
                    <th>Modificar</th>
                    <th>Campos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $tables as $table ) : ?>
                <tr>
                    <th scope="row"><?php echo $table['table_name']; ?></th>
                    <td><input class="regular-text" type="checkbox" name="check_<?php echo $table['table_name'];?>">
                    </td>
                    <td>
                        <select name="<?php echo $table['table_name']; ?>">
                            <option value="none">Escoge algun campo a modificar</option>
                            <?php foreach ( $table['fields'] as $field ) : ?>
                            <option value="<?php echo $field['Field']; ?>"><?php echo $field['Field']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
        <?php wp_nonce_field('update-user-id' ); ?>
        <input type="hidden" name="action" value="update_user_id" />
        <br>
        <input type="submit" class="button button-primary" value="Modificar">
    </form>
</div>