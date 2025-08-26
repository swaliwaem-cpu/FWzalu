<?php

use function ACFCustomDatabaseTables\acf_version_lt;

$data = isset( $data ) ? $data : new stdClass();
$action = isset( $data->action ) ? $data->action : '';
$button_url = isset( $data->button_url ) ? $data->button_url : '';
$fields = isset( $data->fields ) ? $data->fields : [];
?>
<div class="acfcdt-prompt-notice-inner">
	<p>Custom database table definition JSON file was <?php echo $action ?>:</p>
	<?php if ( $fields ): ?>
		<table class="acfcdt-prompt-notice-inner__summary acfcdt-notice-table">
			<?php foreach ( $fields as $field ): ?>
				<tr>
					<th><?php echo $field['title'] ?></th>
					<td><?php echo $field['content'] ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	<p>
		To apply these updates to your database, you need to run the update process on the
		<em>Manage Tables</em> admin screen.
	</p>
	<div class="acfcdt-prompt-notice-inner__action">
		<a class="<?php echo acf_version_lt( 6 ) ? 'button button-primary' : 'acf-btn' ?>"
		   href="<?php echo $button_url ?>">Manage Database Tables</a>
	</div>
</div>