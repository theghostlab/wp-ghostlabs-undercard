<?php


use GHOSTLABS\UNDERCARD\Boot\Edition;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghostlabs_undercard_autoload = GHOSTLABS_UNDERCARD_PLUGIN_PATH . 'vendor/autoload.php';

if ( ! file_exists( $ghostlabs_undercard_autoload ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="error"><p>%s</p></div>',
			esc_html__(
				'Undercard plugin error: the Composer autoloader is missing. Reinstall the plugin — or, if you are running it from source, install its dependencies and rebuild.',
				'ghostlabs-undercard'
			)
		);
	} );

	return;
}

require_once $ghostlabs_undercard_autoload;

foreach ( Edition::libraries() as $ghostlabs_undercard_library ) {
	require_once $ghostlabs_undercard_library;
}

$ghostlabs_undercard_kernel = Edition::kernel();

add_action( 'plugins_loaded', function () use ( $ghostlabs_undercard_kernel ) {
	try {
		$ghostlabs_undercard_kernel::getInstance()->init()->run();
	} catch ( \Exception $e ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- not debug code: a kernel that failed to boot has no screen to report on, and the log is the only place the failure surfaces.
		error_log( '[Undercard] ' . $e->getMessage() );
	}
}, 10 );
