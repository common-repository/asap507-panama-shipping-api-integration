<?php

defined('ABSPATH') || exit;

class AsapWC_Shipping_Method extends WC_Shipping_Method
{

    private $config;

    /**
     * Shipping class
     */
    public function __construct()
    {
        // These title description are display on the configuration page
        $this->id = 'asap-shipping-method';
        $this->title = __('Asap Shipping Method');
        $this->method_title = esc_html__('Asap Shipping Method', 'asap-shipping');
        $this->method_description = esc_html__('Configuración para el método de envio por parte de ASAP, aqui puede indicar si el tipo de calculo, pueda ser fijo o dinamico.', 'asap-shipping');

        // Run the initial method
        $this->init();

        // Initial config
        $this->config = new \Config\Config();
    }

    /**
     ** Load the settings API
     */
    public function init()
    {
        // Add the form fields
        $this->init_form_fields();
        // Load the settings API
        $this->init_settings();

        $this->enabled = $this->settings['enabled'];

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

    }

    public function init_form_fields()
    {
        $getLocations = get_option('wcrsprd_pickup_locations');
        $locationsAry = json_decode($getLocations, true);
        $options = [];
        $vehicles = [
            'car' => 'Car',
            'bike' => 'Bike'
        ];
        $method = [
            'dinamic' => 'Dinámico',
            'permanent' => 'Fijo'
        ];

        foreach ($locationsAry as $arK => $data) {
            $options[$locationsAry[$arK]['nombre']] = $locationsAry[$arK]['nombre'];
        }

        $form_fields = array(

            'enabled' => array(
                'title' => esc_html__('Enable/Disable', 'asap-shipping'),
                'type' => 'checkbox',
                'label' => esc_html__('Enable this shipping method', 'asap-shipping'),
                'default' => 'no'
            ),

            'title' => array(
                'title' => esc_html__('Method Title', 'asap-shipping'),
                'type' => 'text',
                'description' => esc_html__('Enter the method title', 'asap-shipping'),
                'default' => esc_html__('Asap Shipping', 'asap-shipping'),
                'desc_tip' => false,
            ),

            'description' => array(
                'title' => esc_html__('Description', 'asap-shipping'),
                'type' => 'textarea',
                'description' => esc_html__('Enter the Description', 'asap-shipping'),
                'default' => esc_html__('Entrega por servicios de Asap', 'asap-shipping'),
                'desc_tip' => false
            ),

            'fare_method' => array(
                'title' => esc_html__('Tipo de calculo', 'asap-shipping'),
                'type' => 'select',
                'description' => esc_html__('Tipo de calculo', 'asap-shipping'),
                'default' => esc_html__('', 'asap-shipping'),
                'placeholder' => _x('Seleccione una opción', 'placeholder', 'woocommerce'),
                'desc_tip' => false,
                'options' => $method
            ),

            'price_shipping_permanent' => array(
                'title' => esc_html__('Precio fijo', 'asap-shipping'),
                'type' => 'text',
                'description' => esc_html__('Precio fijo', 'asap-shipping'),
                'default' => esc_html__('0.00', 'asap-shipping'),
                'desc_tip' => false,
            ),

            'branch' => array(
                'title' => esc_html__('Sucursal', 'asap-shipping'),
                'type' => 'select',
                'description' => esc_html__('Seleccione una sucursal', 'asap-shipping'),
                'default' => esc_html__('', 'asap-shipping'),
                'placeholder' => _x('Seleccione una sucursal', 'placeholder', 'woocommerce'),
                'desc_tip' => true,
                'options' => $options
            ),

            'type_vehicle' => array(
                'title' => esc_html__('Tipo de vehículo', 'asap-shipping'),
                'type' => 'select',
                'description' => esc_html__('Seleccione un vehiculo', 'asap-shipping'),
                'default' => esc_html__('', 'asap-shipping'),
                'placeholder' => _x('Seleccione un vehiculo', 'placeholder', 'woocommerce'),
                'desc_tip' => true,
                'options' => $vehicles
            ),
        );

        $this->form_fields = $form_fields;
    }

    /**
     ** Calculate Shipping rate
     */
    public function calculate_shipping($package = array())
    {
        // Config DATA Shipping
        wc_clear_notices();
        $branch_store = $this->settings['branch'];
        $vehicle = $this->settings['type_vehicle'];
        $fare_method = $this->settings['fare_method'];
        $price_shipping_permanent = $this->settings['price_shipping_permanent'];
        $api_mode = get_option('wcrsprd_apimode');

        $branch_geolocation = [];
        $cost = '0.00';
        $km = '';
        $destination = $package['destination'];
        $address = $destination['address_1'] . ',' . $destination['city'] . ',' . $destination['state'] . ',' . $destination['country'];

        if ($this->settings['enabled'] !== 'no') {
            if ($fare_method === 'dinamic') {
                $gMapApiKey = get_option('wcrsprd_apigmap');
                $response = wp_remote_get('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $gMapApiKey);
                $responseAry = wp_remote_retrieve_body($response);
                $responseAry = json_decode($responseAry, true);

                if ($responseAry['status'] == 'OK') {
                    $_SESSION['latitude'] = $responseAry['results'][0]['geometry']['location']['lat'];
                    $_SESSION['longitude'] = $responseAry['results'][0]['geometry']['location']['lng'];
                    if (isset($responseAry['results'][0]['partial_match'])) {
                        $_SESSION['partial_match'] = 'Y';
                    }

                    $latitude = $responseAry['results'][0]['geometry']['location']['lat'];
                    $longitude = $responseAry['results'][0]['geometry']['location']['lng'];

                    // Store Data Api
                    $getLocations = get_option('wcrsprd_pickup_locations');
                    $locationsAry = json_decode($getLocations, true);

                    foreach ($locationsAry as $arK => $data) {
                        if ($data['nombre'] === $branch_store) {
                            $branch_geolocation['latitude'] = $data['latitude'];
                            $branch_geolocation['longitude'] = $data['longitude'];
                            $branch_geolocation['direction'] = $data['direction'];
                        }
                    }

                    // Validate coverage area
                    $headers = [
                        'Content-Type' => 'application/json',
                        'x-api-key' => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apikey_live') : get_option('wcrsprd_apikey')
                    ];
                    $params_coverage = [
                        "user_token" => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apitoken_live') : get_option('wcrsprd_apitoken'),
                        "shared_secret" => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apisecret_live') : get_option('wcrsprd_apisecret'),
                        "source_address" => $branch_geolocation['direction'],
                        "source_lat" => $branch_geolocation['latitude'],
                        "source_long" => $branch_geolocation['longitude'],
                        "desti_lat" => $latitude,
                        "desti_long" => $longitude
                    ];
                    $response_coverage = wp_remote_post($this->config->base_url_prod . 'geo/v2/api/geofence', [
                        'body' => json_encode($params_coverage),
                        'headers' => $headers,
                        'timeout' => 50,
                    ]);

                    if (json_decode($response_coverage['body'])->status === true) {
                        // Get Fare
                        $params = [
                            "user_token" => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apitoken_live') : get_option('wcrsprd_apitoken'),
                            "shared_secret" => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apisecret_live') : get_option('wcrsprd_apisecret'),
                            "phone" => ($api_mode === 'https://goasap.app/ecommerce/v2/api') ? get_option('wcrsprd_apiphone_live') : get_option('wcrsprd_apiphone'),
                            "type_id" => 2,
                            "is_personal" => 0,
                            "is_oneway" => 1,
                            "source_address" => $branch_geolocation['direction'],
                            "source_lat" => $branch_geolocation['latitude'],
                            "source_long" => $branch_geolocation['longitude'],
                            "source_seller_name" => "Wordpress store",
                            "source_seller_phone" => "0",
                            "special_inst" => "",
                            "desti_address" => $destination['address'],
                            "desti_lat" => $latitude,
                            "desti_long" => $longitude,
                            "desti_customer_name" => "Customer",
                            "desti_customer_phone" => "0",
                            "dest_special_inst" => "",
                            "request_later" => 0,
                            "request_later_time" => "",
                            "external_order_id" => "0",
                            "vehicle_type" => $vehicle,
                        ];
                        $response2 = wp_remote_post($this->config->base_url_prod . 'ecommerce/v2/api/distancefare', [
                            'body' => json_encode($params),
                            'headers' => $headers,
                            'timeout' => 50,
                        ]);

                        if ($response2['response']['code'] === 200) {
                            $body = json_decode(wp_remote_retrieve_body($response2), true);

                            $cost = $body['fareTotal']['total'];
                            $km = '('.$body['KmTotal']['total'] .' km)';
                        } else {
                            $message_error = "Asap Shipping ha dejado de funcionar, contacte con el administrador: ".json_decode($response2['body'])->message;

                            if( ! wc_has_notice($message_error, 'error') ) {
                                wc_add_notice($message_error, 'error');
                            }
                        }
                    }
                    else {
                        if ($response_coverage['response']['code'] == 401) {
                            $message_error = "Asap Shipping ha dejado de funcionar, contacte con el administrador: " . json_decode($response_coverage['body'])->message.','.json_decode($response_coverage['body'])->err;

                            if (!wc_has_notice($message_error, 'error')) {
                                wc_add_notice($message_error, 'error');
                            }
                        }
                    }
                }
                else {
                    $message_error = "Asap Shipping ha dejado de funcionar, contacte con el administrador: " . $responseAry['error_message'].', '.$responseAry['status'];

                    if (!wc_has_notice($message_error, 'error')) {
                        wc_add_notice($message_error, 'error');
                    }
                }
            } else {
                $cost = $price_shipping_permanent;
            }
        }

        $this->add_rate(array(
            'id' => $this->id,
            'label' => 'Asap Shipping '.$km,
            'cost' => $cost
        ));
    }
}


function hide_asap_shipping_for_order_total($rates)
{
    $shipping = $rates;
    $free = [];

    foreach ($shipping as $rate_id => $rate) {
        if ($rate->id === 'asap-shipping-method' && $rate->cost === '0.00') {
        } else {
            $free[$rate_id] = $rate;
        }
    }
    return $free;
}
add_filter('woocommerce_package_rates', 'hide_asap_shipping_for_order_total', 100);
