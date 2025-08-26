<?php

use ACFCustomDatabaseTables\Tools\ToolBase;
use function ACFCustomDatabaseTables\acf_version_lt;

$data = isset( $data ) ? $data : new stdClass();

/** @var ToolBase[] $tools */
$tools = isset( $data->tools ) ? $data->tools : [];
?>
<div class="acfcdt-admin-wrap">
	<div class="acfcdt-tools">

		<?php foreach ( $tools as $tool ): ?>
			<?php if ( acf_version_lt( 6 ) ): ?>
				<div class="acfcdt-tools__card">
					<h3 class="acfcdt-tools__card-title"><?php echo esc_html( $tool->name() ) ?></h3>
					<div class="acfcdt-tools__card-descr">
						<?php echo wp_kses( $tool->description(), 'post' ) ?>
					</div>
				</div>
			<?php else: ?>
				<div class="acf-box">
					<div class="title">
						<h3><?php echo esc_html( $tool->name() ) ?></h3>
					</div>
					<div class="inside acfcdt-fcmt0 acfcdt-lcmb0">
						<?php echo wp_kses( $tool->description(), 'post' ) ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

	</div>
</div>