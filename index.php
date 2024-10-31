<?php
/**
 * Plugin Name: PDF Embed Block
 * Description: Embed PDF files easily in your pages and posts.
 * Version: 1.0.2
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: pdf-embed-block
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

// Constant
define( 'PEB_PLUGIN_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.2' );
define( 'PEB_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'PEB_DIR_PATH', plugin_dir_path( __FILE__ ) );

// PDF Embed
class PEB_PDFEmbed{
	function __construct(){
		add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
		add_action( 'script_loader_tag', [$this, 'scriptLoaderTag'], 10, 3 );
		add_action( 'init', [$this, 'onInit'] );
	}

	// Block assets
	function enqueueBlockAssets(){
		wp_register_script( 'adobe-viewer', 'https://documentcloud.adobe.com/view-sdk/viewer.js', [], PEB_PLUGIN_VERSION );
	}
	function scriptLoaderTag( $tag, $handle, $src ){
		if($handle === 'adobe-viewer'){
			return "<script src='https://documentcloud.adobe.com/view-sdk/viewer.js'></script>";
		}
		return $tag;
	}

	function onInit() {
		// Plugin Settings
		register_setting(
			'pebPluginSettings',
			'pebAPIKey',
			[
				'default'		=> '',
				'show_in_rest'	=> true,
				'type'			=> 'string'
			]
		);

		// Block registration
		wp_register_style( 'peb-pdf-embed-style', PEB_DIR_URL . 'dist/style.css', [], PEB_PLUGIN_VERSION ); // Style
		wp_register_style( 'peb-pdf-embed-editor-style', PEB_DIR_URL . 'dist/editor.css', [ 'peb-pdf-embed-style' ], PEB_PLUGIN_VERSION ); // Backend Style

		register_block_type( __DIR__, [
			'editor_style'		=> 'peb-pdf-embed-editor-style',
			'render_callback'	=> [$this, 'render']
		] ); // Register Block

		wp_set_script_translations( 'peb-pdf-embed-editor-script', 'pdf-embed-block', PEB_DIR_PATH . 'languages' );
	}

	// Render Block
	function render( $attributes ){
		extract( $attributes );

		wp_enqueue_style( 'peb-pdf-embed-style' );
		wp_enqueue_script( 'peb-pdf-embed-script', PEB_DIR_URL . 'dist/script.js', [ 'react', 'react-dom', 'adobe-viewer' ], false );
		wp_set_script_translations( 'peb-pdf-embed-script', 'pdf-embed-block', PEB_DIR_PATH . 'languages' );

		$className = $className ?? '';
		$blockClassName = "wp-block-peb-pdf-embed $className align$align";

		ob_start(); ?>
		<div class='<?php echo esc_attr( $blockClassName ); ?>' id='pebPDFEmbed-<?php echo esc_attr( $cId ) ?>' data-props='<?php echo esc_attr( wp_json_encode( [ 'attributes' => $attributes, 'pebAPIKey' => get_option( 'pebAPIKey' ) ] ) ); ?>'></div>

		<?php return ob_get_clean();
	} // Render
}
new PEB_PDFEmbed();