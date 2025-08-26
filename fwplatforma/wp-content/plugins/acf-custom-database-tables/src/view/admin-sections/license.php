<?php

use function ACFCustomDatabaseTables\acf_version_lt;

$data = isset( $data ) ? $data : new stdClass();
$license_form = isset( $data->license_form ) ? $data->license_form : '';
?>
<div class="acf-box">
	<div class="title">
		<h3>License</h3>
	</div>
	<div class="<?php echo acf_version_lt( 6 ) ? 'inner' : 'inside' ?>">
		<?php echo $license_form ?>
	</div>
</div>