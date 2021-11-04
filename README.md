# n-framework
Framework for fast development of WordPress Plugins


Example of initialisation the plugin
```php
namespace NMagnet;

use NSukonny\NFramework\Loader;

defined( 'ABSPATH' ) || exit;

define( 'VERSION', '1.0.0' );
define( 'PATH', plugin_dir_path( __FILE__ ) );
define( 'URL', plugin_dir_url( __FILE__ ) );

require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' );
Loader::init_autoload( __NAMESPACE__, __DIR__ );

//Now you can star the plugin
add_action('init', array(NMagnet::class, 'instance'));
```