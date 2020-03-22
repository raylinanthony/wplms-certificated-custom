<?php 
/**
 * Plugin Name
 *
 * @package           WPLMS Certificated by Raylin
 * @author            Raylin Aquino
 * @copyright         2019 raylinaquino.com
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WPLMS Certificated by Raylin
 * Plugin URI:        https://raylinaquino.com
 * Description:       Add a custom certificated
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      5.2
 * Author:            Raylin Aquino
 * Author URI:        https://raylinaquino.com
 * Text Domain:       wplms_cert_ray
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('WPLMS_CERT_CURRENT_DIR', plugin_dir_path( __FILE__ ));
define('WPLMS_CERT_CURRENT_URL', plugin_dir_url( __FILE__ ));

 

require( WPLMS_CERT_CURRENT_DIR.'/woo.class.php');

load_plugin_textdomain( 'wplms_cert_ray', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

new WPLMSCertificated(); 
 