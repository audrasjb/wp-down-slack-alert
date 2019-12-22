<?php
/*
 * Plugin name: WP Down Slack Alert
 * Description: Connect WordPress Recovery Mode to a your Slack team to receive alerts when websites are down
 * Plugin URI: https://www.whodunit.fr/wp-down-slack-alert-wordpress-plugin-notification
 * Requires at least: 5.2
 * Requires PHP: 5.6
 * Author: Whodunit Agency
 * Author URI: https://www.whodunit.fr/
 * Version: 0.2
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text-domain: wp-down-slack-alert
 */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Version management and Worker refresh
function wpdsa_plugin_version() {

	// IMPORTANT: BUMP VERSION NUMBER ON EACH RELEASE
	$wpdsa_version = '0.2';

	$wpdsa_current_version = get_option( 'wpdsa_version' );
	if ( false === $wpdsa_current_version ) {
		update_option( 'wpdsa_version', $wpdsa_version );
	} else {
		if ( version_compare( $wpdsa_current_version, $wpdsa_version, '<' ) ) {
			$filesystem = wpdsa_get_filesystem();
			// Remove existing file
			$filename = WPMU_PLUGIN_DIR . '/wp-down-slack-alert-worker.php';
			if ( $filesystem->exists( $filename ) ) {
				$filesystem->delete( $filename );
			}
			// Add new file
			$contents = $filesystem->get_contents( plugin_dir_path( __FILE__ ) . 'mu-plugin-template/wp-down-slack-alert-worker.php' );
			if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
				$filesystem->mkdir( WPMU_PLUGIN_DIR );
			}
			if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
				return;
			}
			$filesystem->put_contents( $filename, $contents );
			echo 'updated file' . $wpdsa_version . '-' . $wpdsa_current_version;
			update_option( 'wpdsa_version', $wpdsa_version );
		}
	}
	// If the mu-plugin was removed, recreate it.
	$filesystem = wpdsa_get_filesystem();
	$filename   = WPMU_PLUGIN_DIR . '/wp-down-slack-alert-worker.php';
	if ( ! $filesystem->exists( $filename ) ) {
		$contents = $filesystem->get_contents( plugin_dir_path( __FILE__ ) . 'mu-plugin-template/wp-down-slack-alert-worker.php' );
		if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
			$filesystem->mkdir( WPMU_PLUGIN_DIR );
		}
		if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
			return;
		}
		$filesystem->put_contents( $filename, $contents );
	}
}
add_action( 'admin_init', 'wpdsa_plugin_version' );

// Plugin activation
function wpdsa_plugin_activation() {
	$filesystem = wpdsa_get_filesystem();
	$filename   = WPMU_PLUGIN_DIR . '/wp-down-slack-alert-worker.php';
	if ( $filesystem->exists( $filename ) ) {
		return;
	}
	$contents = $filesystem->get_contents( plugin_dir_path( __FILE__ ) . 'mu-plugin-template/wp-down-slack-alert-worker.php' );
	if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
		$filesystem->mkdir( WPMU_PLUGIN_DIR );
	}
	if ( ! $filesystem->exists( WPMU_PLUGIN_DIR ) ) {
		return;
	}
	$filesystem->put_contents( $filename, $contents );
}
register_activation_hook( __FILE__, 'wpdsa_plugin_activation' );

// Plugin deactivation
function wpdsa_plugin_deactivation() {
	$filesystem = wpdsa_get_filesystem();
	// Remove 0.1 file if it exists
	$old_version_filename = WPMU_PLUGIN_DIR . '/wp-down-slack-alert-worker.phps';
	if ( $filesystem->exists( $old_version_filename ) ) {
		$filesystem->delete( $old_version_filename );
	}
	// Remove existing file
	$filename = WPMU_PLUGIN_DIR . '/wp-down-slack-alert-worker.php';
	if ( $filesystem->exists( $filename ) ) {
		$filesystem->delete( $filename );
	}
}
register_deactivation_hook( __FILE__, 'wpdsa_plugin_deactivation' );

// Get filesystem function
function wpdsa_get_filesystem() {
	static $filesystem;
	if ( $filesystem ) {
		return $filesystem;
	}
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

	$filesystem = new WP_Filesystem_Direct( new StdClass() );
	if ( ! defined( 'FS_CHMOD_DIR' ) ) {
		define( 'FS_CHMOD_DIR', ( @fileperms( ABSPATH ) & 0777 | 0755 ) );
	}
	if ( ! defined( 'FS_CHMOD_FILE' ) ) {
		define( 'FS_CHMOD_FILE', ( @fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
	}
	return $filesystem;
}
