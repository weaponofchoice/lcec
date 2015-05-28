<?php
$loc_title = get_field( 'location_title' );
$loc_text = get_field( 'location_text' );
$loc_map = get_field( 'location_map' );
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
  
  <?php include( locate_template('includes/map.php') ); ?>
</section>