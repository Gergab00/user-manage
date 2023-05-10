<div class="wrap">
    <h1>Fusionar Usuarios Duplicados</h1>
    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <td>
                        <?php wp_nonce_field('manage-duplicate-user' ); ?>
                        <input type="hidden" name="action" value="manage_duplicate_user" />
                        <input type="submit" class="button-primary" value="Fusionar">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>