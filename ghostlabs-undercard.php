<?php
/**
 * Undercard — generated plugin entry file (FREE tier).
 *
 * The build script copies this file to the assembled plugin directory as
 * `ghostlabs-undercard.php`. Do not edit the copy in wp-content/plugins — edit
 * this template and rebuild.
 *
 * @package GhostLabsUndercard
 *
 * Plugin Name:       GhostLabs Undercard
 * Plugin URI:        https://theghostlab.io/undercard
 * Description:       Author profile cards as native blocks — guest authors, co-authors, roles and bylines. Four designs, five layouts, light and dark.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            GhostLabs
 * Author URI:        https://theghostlab.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ghostlabs-undercard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHOSTLABS_UNDERCARD_EDITION', 'free' );
define( 'GHOSTLABS_UNDERCARD_VERSION', '1.0.0' );
define( 'GHOSTLABS_UNDERCARD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GHOSTLABS_UNDERCARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GHOSTLABS_UNDERCARD_PLUGIN_SLUG', plugin_basename( __FILE__ ) );

require_once GHOSTLABS_UNDERCARD_PLUGIN_PATH . 'bootstrap.php';
