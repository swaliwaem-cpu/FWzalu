<?php

use function ACFCustomDatabaseTables\acf_version_lt;

$data = isset( $data ) ? $data : new stdClass();
$section_links = isset( $data->section_links ) ? $data->section_links : [];
$current_section = isset( $data->current_section ) ? $data->current_section : '';
$section_content = isset( $data->section_content ) ? $data->section_content : '';
?>
<div class="wrap acf-settings-wrap" id="acf-custom-database-tables">

	<?php // This is no longer visible but leaving it here as it is used to position the error notices ?>
	<h1>Custom Database Tables</h1>

	<?php settings_errors() ?>

	<div class="acfcdt-admin-wrap">

		<div class="acfcdt-admin-nav-wrapper">
			<?php if ( acf_version_lt( 6 ) ): ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $section_links as $section ): ?>
						<a href="<?php echo $section['href'] ?>"
						   title="<?php echo $section['title'] ?>"
						   class="nav-tab <?php echo ( $section['name'] === $current_section ) ? 'nav-tab-active' : '' ?>">
							<?php echo $section['text'] ?>
						</a>
					<?php endforeach; ?>
				</h2>
			<?php else: ?>
				<ul class="acfcdt-tab-group">
					<?php foreach ( $section_links as $section ): ?>
						<li class="acfcdt-tab-group__tab <?php echo ( $section['name'] === $current_section ) ? 'acfcdt-tab-group__tab--active' : '' ?>">
							<a href="<?php echo $section['href'] ?>"
							   title="<?php echo $section['title'] ?>"
							   class="acfcdt-tab-group__btn">
								<?php echo $section['text'] ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div>
			<?php echo $section_content ?>
		</div>
	</div>

</div>