<?php
// Options
$text_o_menu = get_sub_field( 'text_o_menu' );

$text_o_c_pos = get_sub_field( 'text_o_c_pos' );
$text_o_c_align = get_sub_field( 'text_o_c_align' );

// Content
$text_c_caption = get_sub_field( 'text_c_caption' );
$text_c_title = get_sub_field( 'text_c_title' );
$text_c_text = preg_replace( '/<p>/', '<p class="is_aligned-' . $text_o_c_align . '">', get_sub_field( 'text_c_text' ) );
?>
<section class="section_text <?php if( $text_o_menu == true ){ echo 'has_anchor" id="anchor-' . $i_anchor; } else { echo '"'; }; ?>">
  <div class="section_body <?php echo 'is_positioned-' . $text_o_c_pos; ?>">
    <p class="<?php echo 'is_aligned-' . $text_o_c_align; ?>"><?php echo $text_c_caption; ?></p>
    <h3 class="<?php echo 'is_aligned-' . $text_o_c_align; ?>">
      <?php echo $text_c_title; ?>
    </h3>
    <div>
      <?php echo $text_c_text; ?>
    </div>
  </div>
</section>