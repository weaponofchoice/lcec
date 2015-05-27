<?php
// Options
$text_o_connect = get_sub_field( 'text_o_connect' );
$text_o_background = get_sub_field( 'text_o_background' );

// Content
$text_c_caption = get_sub_field( 'text_c_caption' );
$text_c_title = get_sub_field( 'text_c_title' );
$text_c_text = get_sub_field( 'text_c_text' );
?>
<section class="section_text<?php if( $text_o_background ){ echo ' has_bg'; } if( $text_o_connect ){ echo ' is_connected'; } ?>">
  <div class="section_body">
    <p><?php echo $text_c_caption; ?></p>
    <h3><?php echo $text_c_title; ?></h3>
    <div>
      <?php echo $text_c_text; ?>
    </div>
  </div>
</section>