<?php
/**
 * Plugin Name: WooCommerce Stats By Currency
 * Plugin URI: https://lubus.in
 * Description: Add multicurrency stats on dashboard.
 * Author: ajitbohra
 * Author URI: https://lubus.in
 * Version: 1.0.0
 * Text Domain: WSBC
 * Domain Path: /languages
 *
 * @package WSBC
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup Constants
*/
// Plugin version.
if ( ! defined( 'WSBC_VERSION' ) ) {
	define( 'WSBC_VERSION', '1.0.0' );
}
// Plugin Root File.
if ( ! defined( 'WSBC_PLUGIN_FILE' ) ) {
	define( 'WSBC_PLUGIN_FILE', __FILE__ );
}
// Plugin Folder Path.
if ( ! defined( 'WSBC_PLUGIN_DIR' ) ) {
	define( 'WSBC_PLUGIN_DIR', plugin_dir_path( WSBC_PLUGIN_FILE ) );
}
// Plugin Folder URL.
if ( ! defined( 'WSBC_PLUGIN_URL' ) ) {
	define( 'WSBC_PLUGIN_URL', plugin_dir_url( WSBC_PLUGIN_FILE ) );
}
// Plugin Basename aka: "TAF/twitter-api-feed.php".
if ( ! defined( 'WSBC_PLUGIN_BASENAME' ) ) {
	define( 'WSBC_PLUGIN_BASENAME', plugin_basename( WSBC_PLUGIN_FILE ) );
}

/**
 * Return base currency
 *
 * @retrun string
 */
function wsbc_get_base_currency() {
	return get_woocommerce_currency();
}

/**
 * Return installed currencies
 *
 * @return array
 */
function wsbc_get_installed_currencies() {
	$installed_currencies = array();

	foreach ( WCPBC()->get_regions() as $region ) {
		$installed_currencies[] = $region['currency'];
	}

	return array_unique( $installed_currencies );
}

 /**
  * Get sales report data.
  *
  * @return object
  */
function get_sales_report_data_by_currency( $currency ) {

	add_filter(
		'woocommerce_reports_get_order_report_data_args',
		function( $args ) use ( $currency ) {

			$args['where_meta'] = array(
				'relation' => 'OR',
				array(
					'type'       => 'meta',
					'meta_key'   => array( '_order_currency' ),
					'meta_value' => $currency,
					'operator'   => 'LIKE',
				),
			);

			return $args;
		},
		10,
		2
	);

	$sales_by_date                 = new WC_Report_Sales_By_Date();
	$sales_by_date->start_date     = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
	$sales_by_date->end_date       = current_time( 'timestamp' );
	$sales_by_date->chart_groupby  = 'day';
	$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

	return $sales_by_date->get_report_data();
}

/**
 * Add sales this months for currencies
 *
 * @return void
 */
function wsbc_add_stats() {
	if ( current_user_can( 'view_woocommerce_reports' ) ) {
		// For base currency
		$base_currency = wsbc_get_base_currency();
		$sales         = get_sales_report_data_by_currency( $base_currency );
		?>
			<li class="sales-this-month sales-this-month-$base_currency">
				<a href="#">
					<?php printf( __( '<strong>%1$s</strong>net sales this month in %2$s', 'woocommerce' ), wc_price( $sales->net_sales, array( 'currency' => $base_currency ) ), $base_currency ); ?>
				</a>
			</li>
		<?php
		// For additional zone base pricing
		foreach ( wsbc_get_installed_currencies() as $currency ) {
			$sales = get_sales_report_data_by_currency( $currency );
			?>
				<li class="sales-this-month sales-this-month-$currency">
					<a href="#">
						<?php printf( __( '<strong>%1$s</strong>net sales this month in %2$s', 'woocommerce' ), wc_price( $sales->net_sales, array( 'currency' => $currency ) ), $currency ); ?>
					</a>
				</li>
			<?php
		}
	}

}
add_action( 'woocommerce_after_dashboard_status_widget', 'wsbc_add_stats' );

/**
 * Check required plugins
 *
 * @return void
 */

function wsbc_required_plugins() {
	$activated = (array) get_option( 'active_plugins', array() );
	$required  = array(
		'woocommerce/woocommerce.php',
		'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php'
	);

	$required_plugins_activated = count( array_intersect($activated, $required) ) == count( $required );

    if ( !$required_plugins_activated  ) { ?>
   
        <?php
        $style          = '<style type="text/css">
                                .wp-die-message {
                                    border: 1px solid #ccd0d4;
                                    border-left: solid 5px #dc3232;
                                    padding: 12px 0 12px 25px;
                                }
                                .button {
                                    display: inline-block;
                                    text-decoration: none;
                                    font-size: 13px;
                                    line-height: 2.15384615;
                                    min-height: 30px;
                                    margin: 0;
                                    padding: 0 10px;
                                    cursor: pointer;
                                    border-width: 1px;
                                    border-style: solid;
                                    -webkit-appearance: none;
                                    border-radius: 3px;
                                    white-space: nowrap;
                                    box-sizing: border-box;
                                }
                                .primary {
                                    background: #007cba;
                                    border-color: #007cba;
                                    color: #fff;
                                    text-decoration: none;
                                    text-shadow: none;
                                }
                                .primary:hover {
                                    background: #0071a1;
                                    border-color: #0071a1;
                                    color: #fff;
                                }
                            </style>';
        $warning_logo   = '<svg xmlns="http://www.w3.org/2000/svg" width="54px" height="54px" viewBox="0 0 64 64"><defs><linearGradient gradientTransform="matrix(1.31117 0 0 1.30239 737.39 159.91)" gradientUnits="userSpaceOnUse" id="0" y2="-.599" x2="0" y1="45.47"><stop stop-color="#ffc515"/><stop offset="1" stop-color="#ffd55b"/></linearGradient></defs><g transform="matrix(.85714 0 0 .85714-627.02-130.8)"><path d="m797.94 212.01l-25.607-48c-.736-1.333-2.068-2.074-3.551-2.074-1.483 0-2.822.889-3.569 2.222l-25.417 48c-.598 1.185-.605 2.815.132 4 .737 1.185 1.921 1.778 3.404 1.778h51.02c1.483 0 2.821-.741 3.42-1.926.747-1.185.753-2.667.165-4" fill="url(#0)"/><path d="m-26.309 18.07c-1.18 0-2.135.968-2.135 2.129v12.82c0 1.176.948 2.129 2.135 2.129 1.183 0 2.135-.968 2.135-2.129v-12.82c0-1.176-.946-2.129-2.135-2.129zm0 21.348c-1.18 0-2.135.954-2.135 2.135 0 1.18.954 2.135 2.135 2.135 1.181 0 2.135-.954 2.135-2.135 0-1.18-.952-2.135-2.135-2.135z" transform="matrix(1.05196 0 0 1.05196 796.53 161.87)" fill="#000" stroke="#40330d" fill-opacity=".75"/></g></svg>';
        $msg_text       = '<p>Sorry, This plugin requires <strong>WooCommerce</strong> and <strong>WooCommerce Price Based on Country</strong> plugins to work. Learn how to install <strong>WooCommerce Stats By Currency</strong> in the <a href="https://lubus.in/blog/woocommerce-stats-by-currency#guide" target="_blank">guide</a>.</p>';
		$plugins_link   = sprintf( '<a class="button primary" href="%s">Install Plugins</a>', admin_url('plugins.php') );
		$dashboard_link = sprintf( '<a class="button" href="%s">Dashboard</a>', admin_url() );
        $msg            = sprintf( '%s %s %s %s %s', $style, $warning_logo, $msg_text, $plugins_link, $dashboard_link );
        
        wp_die( $msg );
    }
}
register_activation_hook( __FILE__, 'wsbc_required_plugins' );
