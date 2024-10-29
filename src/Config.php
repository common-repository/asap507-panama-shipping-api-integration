<?php

namespace Config;

defined( 'ABSPATH' ) || exit;
class Config {

    // Private variables
    private $api_version = 'v2';

    // Public variables
    public $base_url_dev = 'https://goasap.dev/';
    public $base_url_prod = 'https://goasap.app/';
    public $x_api_key = 'WSMc8GLzq@WBEEwReY22lke2GcVGwRA3wUL@';
    public $assets_url = '/wp-content/plugins/';
    public $mode;
	public $name_plugin_prefix = 'asap507-panama-shipping-api-integration';
    public static $name_business = 'Asap';
    public static $prefix = 'asap-507';

    function __construct() {
        $this->mode = [
            "test" => $this->base_url_dev .'ecommerce/'.$this->api_version.'/api',
            'live' => $this->base_url_prod .'ecommerce/'.$this->api_version.'/api'
        ];

        $this->load_style();
        $this->load_shipping_method_asap();
    }

    public function load_style() {
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'register_scripts_js']);
    }

    public function load_shipping_method_asap() {
        add_action('woocommerce_shipping_init', [$this, 'load_shipping_method']);
        add_filter('woocommerce_shipping_methods', [$this, 'asap_add_shipping_method']);
    }

    public function get_mode_environment($mode) {
        return $this->mode[$mode];
    }

    public function set_img($img) {
        return $this->assets_url.$this->name_plugin_prefix.'/assets/img/'.$img;
    }

    public static function wcgdsrd_add_submission($url, $data, $headers, $customerDataAry)
    {
        $args = [
            'body' => $data,
            'timeout' => '30',
            'headers' => $headers
        ];
        $response = wp_remote_post($url, $args);

        if (wp_remote_retrieve_response_code($response) != 200) {

            $email = get_option('wcrsprd_emasapcerr');

            if (!empty($email)) {
                $to = $email;
                $subject = 'WooCommerce - No se pudo generar la orden en ASAP';
                $body1 = '<html><body><b>Order ID: </b>' . $customerDataAry['oid'];
                $body1 .= '<br><b>Cusomer Name: </b>' . $customerDataAry['cname'];
                $body1 .= '<br><b>Email: </b>' . $customerDataAry['cemail'];
                $body1 .= '<br><b>Phone: </b>' . $customerDataAry['cphone'];
                $body1 .= '<br><b>Date: </b>' . $customerDataAry['date'];
                $body1 .= '<br><b>Error: </b> Could not connect to API.';
                $body1 .= '</body></html>';

                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($to, $subject, $body1, $headers);
            }
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function register_scripts()
    {
        return wp_enqueue_style(Config::$prefix . '-style-admin', '/wp-content/plugins/asap507-panama-shipping-api-integration/assets/css/style.css');
    }

    public function register_scripts_js()
    {
        return wp_enqueue_script(Config::$prefix . '-script-js-admin', '/wp-content/plugins/asap507-panama-shipping-api-integration/assets/js/form-shipping.js', [], '1.0', 'all');
    }

    /**
     * Asap Shipping Methods
     * @return void
     */
    public function load_shipping_method()
    {
        require_once 'ShippingMethods.php';
    }

    public function asap_add_shipping_method($methods)
    {
        $methods[] = 'AsapWC_Shipping_Method';
        return $methods;
    }

}

