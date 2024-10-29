jQuery(document).ready(function ($) {

  const type_calcule_value = $('#woocommerce_asap-shipping-method_fare_method').val()

  if (type_calcule_value === 'dinamic') {
    $('#woocommerce_asap-shipping-method_price_shipping_permanent').attr('readonly', true)
  } else {
    $('#woocommerce_asap-shipping-method_price_shipping_permanent').attr('readonly', false)
  }

  $('#woocommerce_asap-shipping-method_fare_method').on("change", function (e) {
    const value = e.target.value

    if (value === 'dinamic') {
      $('#woocommerce_asap-shipping-method_price_shipping_permanent').attr('readonly', true)
    } else {
      $('#woocommerce_asap-shipping-method_price_shipping_permanent').attr('readonly', false)
    }
  });
})


// woocommerce_asap-shipping-method_price_shipping_permanent
