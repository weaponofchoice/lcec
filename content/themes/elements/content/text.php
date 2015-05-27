<?php
// Options
$text_o_connect = get_sub_field( 'text_o_connect' );

// Content
$text_c_caption = get_sub_field( 'text_c_caption' );
$text_c_title = get_sub_field( 'text_c_title' );
$text_c_text = get_sub_field( 'text_c_text' );
?>
<section class="section_text fadeIn-up">
  <div class="section_body">
    <p><?php echo $text_c_caption; ?></p>
    <h3><?php echo $text_c_title; ?></h3>
    <div>
      <?php echo $text_c_text; ?>
    </div>
  </div>
</section>