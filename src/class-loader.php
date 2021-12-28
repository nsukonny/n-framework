<?php
/**
 * Loader for includes, classes and assets
 */

namespace NSukonny\NFramework;

class Loader {

	use Singleton;

	static $autoload_namespaces = [];

	public function init() {

		$this->load_assets();

		if ( defined( 'FEATURES' ) && is_array( FEATURES ) ) {
			$this->load_features();
		}

	}

	/**
	 * Init classes, assets and other
	 *
	 * @param $namespace
	 * @param $dir_path
	 *
	 * @since 1.0.0
	 */
	public static function init_autoload( $namespace, $dir_path ) {

		self::$autoload_namespaces[] = array(
			'namespace' => $namespace,
			'dirpath'   => $dir_path,
		);

		spl_autoload_register( __CLASS__ . '::autoload' );

		add_action( 'init', array( Loader::class, 'instance' ) );

	}

	/**
	 * Include files from called class
	 *
	 * @param string $class Namespace for needed class
	 *
	 * @since 1.0.0
	 */
	public static function autoload( $class ) {

		$class_explode = explode( '\\', $class );
		if ( ! self::is_for_framework( $class_explode ) ) {
			return;
		}

		$namespace_key = array_search( $class_explode[0], array_column( self::$autoload_namespaces, 'namespace' ) );
		$includes_path = self::$autoload_namespaces[ $namespace_key ]['dirpath'] . '/includes';
		$file_path     = '';
		$file_types    = array( 'class', 'trait', 'abstract' );
		$file_name     = strtolower( str_replace( '_', '-', array_pop( $class_explode ) ) ) . '.php';

		foreach ( $class_explode as $key => $classname ) {
			if ( 0 !== $key ) {
				$file_path .= '/' . strtolower( str_replace( '_', '-', $classname ) );
			}
		}

		foreach ( $file_types as $type ) {
			$filename = $includes_path . $file_path . '/' . $type . '-' . $file_name;

			if ( file_exists( $filename ) ) {
				require_once $filename;

				return;
			}
		}
	}

	/**
	 * Check if we need use framework for that call
	 *
	 * @param array $class_explode Called class name exploaded by \
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_for_framework( array $class_explode ): bool {

		if ( empty( self::$autoload_namespaces ) ) {
			return false;
		}

		$namespaces = array_column( self::$autoload_namespaces, 'namespace' );

		return in_array( $class_explode[0], $namespaces, true );
	}

	/**
	 * Find and enqueue admin styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {

		if ( count( self::$autoload_namespaces ) ) {
			foreach ( self::$autoload_namespaces as $autoload_namespace ) {

				$namespace       = $autoload_namespace['namespace'];
				$lower_namespace = strtolower( $namespace );

				if ( defined( $namespace . '\ENQUEUE' ) ) {
					if ( ! empty( constant( $namespace . '\ENQUEUE' )['admin_styles'] ) ) {
						$this->load_additional_styles( constant( $namespace . '\ENQUEUE' )['admin_styles'] );
					}

					if ( ! empty( constant( $namespace . '\ENQUEUE' )['admin_scripts'] ) ) {
						$this->load_additional_scripts( constant( $namespace . '\ENQUEUE' )['admin_scripts'] );
					}
				}

				$this->enqueue_features( $namespace, $lower_namespace );

				$enqueue = $this->enqueue_style( $lower_namespace, 'assets/css/admin.min.css', $namespace );
				if ( ! $enqueue ) {
					$this->enqueue_style( $lower_namespace, 'assets/css/admin.css', $namespace );
				}

				$enqueue = $this->enqueue_script( $lower_namespace, 'assets/js/admin.min.js', $namespace );
				if ( ! $enqueue ) {
					$this->enqueue_script( $lower_namespace, 'assets/js/admin.js', $namespace );
				}
			}
		}

	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		if ( count( self::$autoload_namespaces ) ) {
			foreach ( self::$autoload_namespaces as $autoload_namespace ) {

				$namespace       = $autoload_namespace['namespace'];
				$lower_namespace = strtolower( $namespace );

				if ( defined( $namespace . '\ENQUEUE' ) ) {
					if ( ! empty( constant( $namespace . '\ENQUEUE' )['styles'] ) ) {
						$this->load_additional_styles( constant( $namespace . '\ENQUEUE' )['styles'] );
					}

					if ( ! empty( constant( $namespace . '\ENQUEUE' )['scripts'] ) ) {
						$this->load_additional_scripts( constant( $namespace . '\ENQUEUE' )['scripts'] );
					}
				}
				$this->enqueue_features( $namespace, $lower_namespace );

				$enqueue = $this->enqueue_style( $lower_namespace, 'assets/css/style.min.css', $namespace );
				if ( ! $enqueue ) {
					$this->enqueue_style( $lower_namespace, 'assets/css/style.css', $namespace );
				}

				$enqueue = $this->enqueue_script( $lower_namespace, 'assets/js/script.min.js', $namespace );
				if ( ! $enqueue ) {
					$this->enqueue_script( $lower_namespace, 'assets/js/script.js', $namespace );
				}
			}
		}

	}

	/**
	 * Find and load default assets
	 *
	 * @since 1.0.0
	 */
	private function load_assets() {

		if ( ! function_exists( 'is_admin' ) ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 16 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 16 );
		}

		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

	}

	/**
	 * Load requested features
	 *
	 * @since 1.0.0
	 */
	private function load_features() {

		foreach ( FEATURES as $feature ) {
			$filename = __DIR__ . '/features/class-' . $feature . '.php';

			if ( file_exists( $filename ) ) {
				require_once $filename;

				return;
			}
		}

	}

	/**
	 * Check js script and add it to system
	 *
	 * @param string $slug Slug name for enqueue.
	 * @param string $css_file Path to js file from plugin folder.
	 * @param string $namespace Namespace for working PATH and other defined variables
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function enqueue_style( string $slug, string $css_file, string $namespace = '' ): bool {

		if ( file_exists( constant( $namespace . '\PATH' ) . $css_file ) ) {
			wp_enqueue_style(
				$slug,
				constant( $namespace . '\URL' ) . $css_file,
				array(),
				constant( $namespace . '\VERSION' )
			);

			return true;
		}

		return false;
	}

	/**
	 * Check js script and add it to system
	 *
	 * @param string $slug Slug name for enqueue.
	 * @param string $js_file Path to js file from plugin folder.
	 * @param string $namespace Namespace for understanding for what plugin we load this files
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function enqueue_script( string $slug, string $js_file, string $namespace = '' ): bool {

		if ( file_exists( constant( $namespace . '\PATH' ) . $js_file ) ) {
			wp_register_script(
				$slug,
				constant( $namespace . '\URL' ) . $js_file,
				array( 'jquery' ),
				constant( $namespace . '\VERSION' )
			);

			wp_enqueue_script( $slug );

			return true;
		}

		return false;
	}

	/**
	 * @param array $scripts List of scripts for loading
	 *
	 * @since 1.0.0
	 */
	private function load_additional_scripts( array $scripts ) {

		foreach ( $scripts as $slug => $script ) {
			wp_register_script(
				$slug,
				$script,
				array( 'jquery' )
			);
			wp_enqueue_script( $slug );
		}

	}

	/**
	 * @param array $styles List of styles for loading
	 *
	 * @since 1.0.0
	 */
	private function load_additional_styles( array $styles ) {

		foreach ( $styles as $slug => $style ) {
			wp_enqueue_style(
				$slug,
				$style
			);
		}

	}

	/**
	 * Load additional libraries from the Framework
	 *
	 * @param $namespace
	 * @param $lower_namespace
	 */
	public function enqueue_features( $namespace, $lower_namespace ) {

		if ( defined( $namespace . '\FEATURES' )
		     && ! empty( constant( $namespace . '\FEATURES' ) )
		     && is_array( constant( $namespace . '\FEATURES' ) ) ) {

			$enqueue = $this->enqueue_script( 'n-framework', 'vendor/nsukonny/n-framework/src/assets/js/n-framework.min.js', $namespace );
			if ( ! $enqueue ) {
				$this->enqueue_script( 'n-framework', 'vendor/nsukonny/n-framework/src/assets/js/n-framework.min.js', $namespace );
			}

			foreach ( constant( $namespace . '\FEATURES' ) as $feature ) {
				$enqueue = $this->enqueue_style( $lower_namespace, 'vendor/nsukonny/n-framework/src/assets/css/' . $feature . '.min.css', $namespace );
				if ( ! $enqueue ) {
					$this->enqueue_style( $lower_namespace, 'vendor/nsukonny/n-framework/src/assets/css/' . $feature . '.css', $namespace );
				}
			}
		}
	}
}