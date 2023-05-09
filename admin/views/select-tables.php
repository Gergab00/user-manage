<div class="wrap">
    <h1>Cambiar IDs de todos los usuarios en rango</h1>
    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="posts_per_page">Inicio de nuevos IDs</label>
                    </th>
                    <td>
                        <input name="start_count_ids" type="number" step="1" min="1" id="start_count_ids" value="200"
                            class="regular-text"> A partir de este numero se generaran los nuevos IDs
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="range_start">Rango de IDs a modificar</label>
                    </th>
                    <td>
                        Entre
                        <input name="range_start" type="number" step="1" min="1" id="range_start" value="1"
                            class="small-text"> y
                        <input name="range_end" type="number" step="1" min="1" id="range_end" value="999"
                            class="small-text"> IDs a modificar
                    </td>
                    <td>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="site">Sitio a modificar</label>
                    </th>
                    <td>
                        Entre
                        <input name="site" type="number" step="1" min="1" id="site" value="1"
                            class="small-text">
                    </td>
                    <td>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>Tabla</th>
                    <th>Campos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $tables as $table ) : ?>
                <tr>
                    <th scope="row"><?php echo $table['table_name']; ?></th>
                    </td>
                    <td>
                        <select name="<?php echo $table['table_name']; ?>">
                            <?php $user_id_found = false; ?>
                            <?php foreach ( $table['fields'] as $field ) : ?>
                            <?php if ( $field['Field'] == 'user_id' ) : ?>
                            <option value="<?php echo $field['Field']; ?>"
                                <?php selected( $field['Field'], $table['selected_field'] ); ?>>
                                <?php echo $field['Field']; ?></option>
                            <?php $user_id_found = true; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ( ! $user_id_found ) : ?>
                            <option value="none">Escoge algún campo a modificar</option>
                            <?php endif; ?>
                            <?php foreach ( $table['fields'] as $field ) : ?>
                            <?php if ( $field['Field'] != 'user_id' ) : ?>
                            <option value="<?php echo $field['Field']; ?>"
                                <?php selected( $field['Field'], $table['selected_field'] ); ?>>
                                <?php echo $field['Field']; ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ( $user_id_found ) : ?>
                            <option value="none">Escoge algún campo a modificar</option>
                            <?php endif; ?>
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