<?php
/**
 * Plugin Name: WooCommerce Stats By Currency
 * Plugin URI: https://lubus.in
 * Description: WooCommerce Stats By Currency is WordPress plugin by Lubus that shows you your WooCommerce stats based on your location at your own WordPress Dashboard.
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
