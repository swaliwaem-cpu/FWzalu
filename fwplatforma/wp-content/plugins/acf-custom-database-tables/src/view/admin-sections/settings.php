<?php

use ACFCustomDatabaseTables\Options;
use function ACFCustomDatabaseTables\acf_version_lt;

?>
<div class="acf-box">
	<div class="title">
		<h3>Settings</h3>
	</div>
	<div class="<?php echo acf_version_lt( 6 ) ? 'inner' : 'inside' ?>">

		<form action="<?php echo esc_attr( admin_url( 'options.php' ) ) ?>" method="post">

			<?php settings_fields( Options::OPTION_GROUP ); ?>

			<?php do_settings_sections( Options::PAGE ) ?>
			
			<input type="submit" value="Save Changes" class="<?php echo acf_version_lt( 6 ) ? 'primary' : 'acf-btn' ?>">

			<a class="acfcdt-reset-defaults-link hide-if-no-js"
			   style="color: #a00; text-decoration:underline;line-height: 30px; margin-left: 30px;"
			   href="#">Reset to defaults</a>

		</form>

	</div>
</div>