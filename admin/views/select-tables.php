<div class="wrap">
    <h1>Custom Plugin</h1>
    <form method="post" action="">
        <?php wp_nonce_field( 'custom-plugin' ); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Tabla</th>
                    <th>Campos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $tables as $table ) : ?>
                <tr>
                    <td><?php echo $table['table_name']; ?></td>

                    <td>
                        <select name="<?php echo $table['table_name']; ?>">
                            <?php foreach ( $table['fields'] as $field ) : ?>
                            <option value="<?php echo $field['Field']; ?>"><?php echo $field['Field']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
        <?php wp_nonce_field('update-users-ids' ); ?>
        <input type="hidden" name="action" value="update_users_ids" />
        <input type="submit" class="button-primary" value="Cambiar IDs">
    </form>
</div>