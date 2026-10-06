<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'add_action' ) ) {
	require_once __DIR__ . '/autoload.php';
	return;
}

if ( ! class_exists( 'GhostLabsWPKitVersions', false ) ) {

	class GhostLabsWPKitVersions {

		private static ?self $instance = null;

		private array $versions = [];

		public function register( string $version, callable $initializer ): void {
			if ( ! isset( $this->versions[ $version ] ) ) {
				$this->versions[ $version ] = $initializer;
			}
		}

		public function latestVersion(): ?string {
			$keys = array_keys( $this->versions );
			if ( empty( $keys ) ) {
				return null;
			}
			usort( $keys, 'version_compare' );
			return end( $keys );
		}

		public static function instance(): self {
			return self::$instance ??= new self();
		}

		public static function initializeLatest(): void {
			$self   = self::instance();
			$latest = $self->latestVersion();

			if ( $latest !== null && isset( $self->versions[ $latest ] ) ) {
				call_user_func( $self->versions[ $latest ] );
			}
		}
	}

	add_action( 'plugins_loaded', [ 'GhostLabsWPKitVersions', 'initializeLatest' ], 0 );
}

GhostLabsWPKitVersions::instance()->register( '1.0.2', function () {
	require_once __DIR__ . '/autoload.php';
} );
