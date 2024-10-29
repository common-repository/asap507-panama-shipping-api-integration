<?php

//Add Meta box to order
add_action( 'add_meta_boxes', 'wcgdsrd_asap_metabox' );
add_action( 'wp_ajax_wcgdsrd_callasap', 'wcgdsrd_create_order_asap_order_complete' );

function wcgdsrd_formatted_shipping_address($order)
{
    if(!empty($order->shipping_address_1)) {

        return
            $order->shipping_address_1 . ', ' .
            $order->shipping_address_2 . ' ' .
            $order->shipping_city      . ', ' .
            $order->shipping_state;
    }
}
function wcgdsrd_asap_metabox()
{
	add_meta_box( 'wcgdsrd_asap_fields', __('ASAP 507 Shipping','woocommerce'), 'wcgdsrd_asap_order_fields', 'shop_order', 'normal','default' );
}
function wcgdsrd_asap_order_fields()
{
    global $post;

    $getLocations = get_option('wcrsprd_pickup_locations');
    $locationsAry = array();

    if (!empty($getLocations)) {
        $locationsAry = json_decode($getLocations, true);
    }

    $asapId = get_post_meta($post->ID, 'asap_delivery_id', true);
    $order = new WC_Order($post->ID);

    $asap_pickup_location = get_post_meta($post->ID, 'asap_pickup_location', true);
    $new_dest_address = get_post_meta($post->ID, 'dest_address', true);
    $destination_address = wcgdsrd_formatted_shipping_address($order);
    $asap_laitude_dest = get_post_meta($post->ID, 'asap_laitude_dest', true);
    $new_dest_lat = get_post_meta($post->ID, 'dest_latitude', true);
    $asap_longitude_dest = get_post_meta($post->ID, 'asap_longitude_dest', true);
    $new_dest_lon = get_post_meta($post->ID, 'dest_longitude', true);
    $asap_vehicle_type = get_post_meta($post->ID, 'asap_vehicle_type', true);

    foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
        $shipping_item_data = $shipping_item_obj->get_data();
        $shipName = trim($shipping_item_data['name']);
        $shipMethod = trim($shipping_item_data['method_title']);

        if (strtolower($shipName) == 'asap' or strtolower($shipMethod) == 'asap') {
            $goAhead = 1;
            break;
        }
    }

    include __DIR__.'/widgets/widget_orders.php';
}
