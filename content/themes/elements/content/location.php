<?php
$loc_title = get_sub_field( 'location_title' );
$loc_text = get_sub_field( 'location_text' );
$loc_map = get_sub_field( 'location_map' );
?>
<section class="section_location">
  <div class="section_header">
    <h3><?php echo $loc_title; ?></h3>
    <?php echo $loc_text; ?>
  </div>
  
  <div class="section_body">
    <div id="map">
      <div class="marker" data-lat="<?php echo $loc_map['lat']; ?>" data-lng="<?php echo $loc_map['lng']; ?>">
      </div>
    </div>
  </div>
  
  <!-- Map functionality -->
  <script type="text/javascript">
  function initialize(){
    var myLatlng = new google.maps.LatLng(<?php echo $loc_map['lat'] . ',' . $loc_map['lng']; ?>);
    var mapOptions = {
      scrollwheel: false,
      zoom: 16,
      center: myLatlng
    }
    var map = new google.maps.Map(document.getElementById('map'), mapOptions);

    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map
    });
  }

  google.maps.event.addDomListener(window, 'load', initialize);
  
  google.maps.event.addListenerOnce(map, 'idle', function() {
      google.maps.event.trigger(map, 'resize');
  });
  </script>
</section>