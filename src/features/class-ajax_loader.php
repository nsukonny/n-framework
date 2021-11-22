<?php
/**
 * Actions for Feature ajax_loader
 *
 * @since 1.0.0
 */

namespace NSukonny\NFramework;

defined( 'ABSPATH' ) || exit;

class Ajax_Loader {

	use Singleton;

	/**
	 * Init feature ajax_loader
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 17 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 17 );
		}

		add_action( 'wp_ajax_nframework_ajax_loader', array( $this, 'upload_files' ) );
		add_action( 'wp_ajax_nopriv_nframework_ajax_loader', array( $this, 'upload_files' ) );

	}

	/**
	 * Upload images to the library
	 *
	 * @since 1.0.0
	 */
	public function upload_files() {

		check_ajax_referer( 'ajax-loader-nonce' );

		$allowed = array( 'png', 'svg' );

		foreach ( $_FILES as $file ) {
			if ( is_array( $file ) ) {
				$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
				if ( in_array( $ext, $allowed ) ) {
					$attach_id = $this->upload_file_to_library( $file );
					$response  = array(
						'attach_id' => $attach_id,
						'url'       => esc_url( wp_get_attachment_image_src( $attach_id )[0] ),
					);

					wp_send_json_success( $response );
				}
			}
		}

		wp_send_json_error();
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$loader = Loader::instance();

		$enqueue = $loader->enqueue_script( 'n-framework-ajax-loader', 'vendor/nsukonny/n-framework/src/assets/js/features/ajax-loader.min.js' );
		if ( ! $enqueue ) {
			$loader->enqueue_script( 'n-framework-ajax-loader', 'vendor/nsukonny/n-framework/src/assets/js/features/ajax-loader.js' );
		}

		wp_localize_script(
			'n-framework-ajax-loader',
			'nframework_ajax_loader',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'_ajax_nonce' => wp_create_nonce( 'ajax-loader-nonce' ),
			)
		);

	}

	/**
	 * Add new image to library
	 *
	 * @param array $file
	 *
	 * @return false|int|\WP_Error
	 *
	 * @since 1.0.0
	 */
	private function upload_file_to_library( $file = array() ) {

		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );
		if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
			$filename      = $file_return['file'];
			$attachment    = array(
				'post_mime_type' => $file_return['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => $file_return['url']
			);
			$attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			if ( 0 < intval( $attachment_id ) ) {
				return $attachment_id;
			}
		}

		return false;
	}

}

add_action( 'init', array( Ajax_Loader::class, 'instance' ), 11 );