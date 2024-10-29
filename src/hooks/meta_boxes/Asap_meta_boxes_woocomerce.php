<?php

//Add Meta box to order
add_action( 'add_meta_boxes', 'wcgdsrd_asap_metabox', 20, 2);
function wcgdsrd_asap_metabox($page)
{
    $screen = wc_get_page_screen_id( 'shop-order' );
	add_meta_box( 'wcgdsrd_asap_metabox', __('ASAP 507 Shipping','webkul'), 'wcgdsrd_asap_order_fields', $screen, 'side','default' );
}
function wcgdsrd_asap_order_fields($post)
{
    $order_id = null;
    $shipping_method_asap = new AsapWC_Shipping_Method();
    $vehicle = $shipping_method_asap->settings['type_vehicle'];

    $getLocations = get_option('wcrsprd_pickup_locations');
    $locationsAry = array();

    if (!empty($getLocations)) {
        $locationsAry = json_decode($getLocations, true);
    }

    // Validate if id POST order
    if ($post instanceof WC_Order) {
        // Obtiene el ID de la orden usando el método get_id()
        $order_id = $post->get_id();
    } elseif ($post instanceof WP_Post && 'shop_order' === $post->post_type) {
        // Alternativa: Si $post es un objeto WP_Post, convierte el ID de la orden
        $order = wc_get_order($post->ID);
        if ($order) {
            $order_id = $order->get_id();
        }
    }

    $asapId = get_post_meta($order_id, 'asap_delivery_id', true);
	$order = new WC_Order($order_id);
	if ($order) {
		$asap_pickup_location = get_post_meta($order_id, 'asap_pickup_location', true);
		$new_dest_address = get_post_meta($order_id, 'dest_address', true);
		$destination_address = wcgdsrd_formatted_shipping_address($order);
		$asap_laitude_dest = get_post_meta($order_id, 'asap_latitude_dest', true);
		$new_dest_lat = get_post_meta($order_id, 'dest_latitude', true);
		$asap_longitude_dest = get_post_meta($order_id, 'asap_longitude_dest', true);
		$new_dest_lon = get_post_meta($order_id, 'dest_longitude', true);
		$asap_vehicle_type = $vehicle;

		foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
			$shipping_item_data = $shipping_item_obj->get_data();
			$shipName = trim($shipping_item_data['name']);
			$shipMethod = trim($shipping_item_data['method_title']);

			if (strtolower($shipName) == 'asap' or strtolower($shipMethod) == 'asap') {
				$goAhead = 1;
				break;
			}
		}
		include __DIR__.'/../../widgets/widget_orders.php';
	}
}
function wcgdsrd_formatted_shipping_address($order)
{
    if(!empty($order->get_shipping_address_1())) {
        return
            $order->get_shipping_address_1() . ', ' .
            $order->get_shipping_address_2() . ' ' .
            $order->get_shipping_city()      . ', ' .
            $order->get_shipping_state();
    }
}
