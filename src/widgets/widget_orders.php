<?php
/**
 * Widget Order to application order
 *
 * @global $asap_pickup_location
 * @global $destination_address
 * @global $asap_laitude_dest
 * @global $asap_longitude_dest
 * @global $asap_vehicle_type
 * @global $post
 * @global $new_dest_address
 * @global $new_dest_lat
 * @global $new_dest_lon
 */

use Config\Config;

$if_checked = (!empty($asapId)) ? 'checked disabled' : ((isset($goAhead)) ? 'checked': '');
  $disable = (!empty($asapId)) ? 'disabled' : '';
  $config = new Config;
?>
<div class="">
  <img src="<?= $config->set_img('banner-asap.png')?>" alt="" width="100%">
</div>

<div id="errDiv" style="color: #B8001C; font-size: 1.1rem;"></div>
<table class="" border="0" cellpadding="10">
  <tr>
    <td><h3>Utilizar ASAP:</h3></td>
    <td>
      <label>
        <input type="checkbox" id="use_asap" value="1" <?= $if_checked; ?> /> Sí</label>
    </td>
  </tr>
  <tr>
    <td>
      <h3>Origen:</h3>
    </td>
    <td>
      <select id="asap_pickup_location" <?= $disable; ?>>
          <?php
          if (!empty($locationsAry)) {
              $i = 1;
              foreach ($locationsAry as $arK => $lAry) {
                  $location = "pl_$i";
                  $selected = '';
                  if (!empty($asapId)) {
                      if ($location === $asap_pickup_location) {
                          $selected = ' selected';
                      }
                  }
                  echo '<option value="' . $arK . '"' . $selected . '>' . $lAry['nombre'] . '--> ' . $lAry['direction'] . '</option>';
                  $i++;
              }
          }
          ?>
      </select>
    </td>
  </tr>


    <?php if (empty($asapId)) {

        ?>
      <tr>
        <td><h3>Dirección de Destino:</h3></td>
        <td><input type="text" id="dest_address"
                   value="<?php echo $destination_address; ?>" <?php if (!empty($asapId)) {
                echo 'disabled';
            } ?> style="width: 100%;"/></td>
      </tr>

      <tr>
        <td><h3>Latitud de Destino:</h3></td>
        <td><input type="text" id="dest_latitude"
                   value="<?php echo $asap_laitude_dest; ?>" <?php if (!empty($asapId)) {
                echo 'disabled';
            } ?> /></td>
      </tr>

      <tr>
        <td><h3>Longitud de Destino:</h3></td>
        <td><input type="text" id="dest_longitude"
                   value="<?php echo $asap_longitude_dest; ?>" <?php if (!empty($asapId)) {
                echo 'disabled';
            } ?> /></td>
      </tr>

      <tr>
        <td>
          <h3>Tipo de Vehículo:</h3>
        </td>
        <td>
            <?php
            $selectedBike = '';
            $selectedCar = '';

            if (!empty($asapId)) {
                if (($asap_vehicle_type == 'bike')) {
                    $selectedBike = ' selected';
                }
                if (($asap_vehicle_type == 'car')) {
                    $selectedCar = ' selected';
                }

            }
            ?>
          <select id="asap_vehicle_type" <?php if (!empty($asapId)) echo 'disabled'; ?>>
            <option value="bike" <?php echo $selectedBike; ?>>Moto</option>
            <option value="car" <?php echo $selectedCar; ?>>Carro</option>
          </select>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <button type="button" id="updt_asap_btn" class="btn btn-primary button button-primary request_asap" value="Solicitar ASAP" style="cursor:pointer;">
            Solicitar ASAP
            <i class="fa fa-spinner fa-spin hidden"></i>
          </button>
        </td>
      </tr>

    <?php } else {
        $trackingLink = get_post_meta($order_id, 'asap_tracking_link', true);
        ?>

      <tr>
        <td><h3>ASAP Delivery ID:</h3></td>
        <td><?php echo $asapId; ?></td>
      </tr>
      <tr>
        <td><h3>Dirección de Destino:</h3></td>
        <td><?php echo $new_dest_address; ?></td>
      </tr>
      <tr>
        <td><h3>Latitud de Destino:</h3></td>
        <td><?php echo $new_dest_lat; ?></td>
      </tr>
      <tr>
        <td><h3>Longitud de Destino:</h3></td>
        <td><?php echo $new_dest_lon; ?></td>
      </tr>
      <tr>
        <td><h3>Tipo de Vehículo:</h3></td>
        <td>
            <?php
            if ($asap_vehicle_type == 'bike') {
                echo 'Moto';
            }
            if ($asap_vehicle_type == 'car') {
                echo 'Carro';
            }
            ?>
        </td>
      </tr>

      <tr>
        <td><h3>ASAP Tracking Link:</h3></td>
        <td><a href="<?php echo $trackingLink; ?>" target="_blank">Click here</a></td>
      </tr>
        <?php
        if (get_post_meta($order_id, 'cancelOrder', true) != 'Y') { ?>
          <tr>
            <td colspan="2">
              <input type="hidden" name="hid_pid" value="<?php echo $order_id; ?>"/>
              <button type="button" id="cancl_asap_btn" class="btn btn-danger button button-danger">
                <i class="fa fa-spinner fa-spin hidden"></i>
                Cancel Order
              </button>
            </td>
          </tr>

        <?php } else {
            echo '<tr>
          <td colspan="2"><strong style="color:red;">Solicitud cancelada.</strong></td>
        </tr>';
        }
    } ?>
</table>
<input type="hidden" name="hid_pid" value="<?= $order_id; ?>"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    .hidden {
        display: none;
    }
</style>
<script>
  jQuery(document).ready(function ($) {
    $('#updt_asap_btn').on('click', function () {
      $('.fa-spin').css('display', 'inline-flex')
      $('#updt_asap_btn').attr("disabled", true)

      $('#errDiv').html('');
      if ($('#asap_pickup_location').val() == '') {
        $('#errDiv').html("Please select pickup location");
        $('.fa-spin').css('display', 'none');
        $('#updt_asap_btn').attr("disabled", false);
        return false;
      }

      if ($('#use_asap:checked').length < 1) {
        $('#errDiv').html("Please check the Use Asap checkbox.");
        $('.fa-spin').css('display', 'none');
        $('#updt_asap_btn').attr("disabled", false);
        return false;
      }

      if ($('#dest_address').val() == '') {
        $('#errDiv').html("Please enter destination address.");
        $('.fa-spin').css('display', 'none');
        $('#updt_asap_btn').attr("disabled", false);
        return false;
      }

      if ($('#dest_latitude').val() == '') {
        $('#errDiv').html("Please enter destination latitude.");
        $('.fa-spin').css('display', 'none');
        $('#updt_asap_btn').attr("disabled", false);
        return false;
      }

      if ($('#dest_longitude').val() == '') {
        $('#errDiv').html("Please enter destination longitude.");
        $('.fa-spin').css('display', 'none');
        $('#updt_asap_btn').attr("disabled", false);
        return false;
      }

      /*if($('#asap_vehicle_type').val()==''){
                $('#errDiv').html("Por favor seleccionar el tipo de vehículo.");
                return false;
            }*/

      $.ajax({
        type: "POST",
        url: '<?php echo site_url() . '/wp-admin/admin-ajax.php'; ?>',
        data: {
          hid_pid: $('input[name=hid_pid]').val(),
          action: 'wcgdsrd_callasap',
          asap_pickup_location: $('#asap_pickup_location').val(),
          use_asap: '1',
          dest_address: $('#dest_address').val(),
          dest_latitude: $('#dest_latitude').val(),
          dest_longitude: $('#dest_longitude').val(),
          asap_vehicle_type: $('#asap_vehicle_type').val()
        },//$('#asapship_ordform').serialize()
        cache: false,
        success: function (response) {
          var respData = JSON.parse(response);
          if (respData.status == 'success') {
            window.location.reload();
          } else {
            $('#errDiv').html(respData.msg);
            $('.fa-spin').css('display', 'none');
            $('#updt_asap_btn').attr("disabled", false);
          }
        }
      });
    });


    $('#cancl_asap_btn').on('click', function () {
      $('.fa-spin').css('display', 'inline-flex')
      $('#cancl_asap_btn').attr("disabled", true)
      $('#errDiv').html('');

      $.ajax({
        type: "POST",
        url: '<?php echo site_url() . '/wp-admin/admin-ajax.php'; ?>',
        data: {
          hid_pid: $('input[name=hid_pid]').val(),
          action: 'wcgdsrd_callasap',
          cancel_order: 'cancelled'
        },
        cache: false,
        success: function (response) {
          var response = JSON.parse(response);
          if (response.status == 'success') {
            window.location.reload();
          } else {
            $('.fa-spin').css('display', 'none');
            $('#updt_asap_btn').attr("disabled", false);
            $('#errDiv').html(response.msg);
          }
        }
      });
    });

  });
</script>
