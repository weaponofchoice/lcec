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
      <?php
      echo $text_c_text;
      
      if( have_rows('text_c_links') ):
        while( have_rows('text_c_links') ): the_row();
          $text_c_link = get_sub_field( 'text_c_links_link' );
          $text_c_link_text = get_sub_field( 'text_c_links_text' );
          ?>
          <a class="link-arrow is_bold" href="<?php echo $text_c_link; ?>"><?php echo $text_c_link_text; ?><img src="<?php echo get_template_directory_uri(); ?>/img/arrow-pri-blue.svg"><br></a>
          <?php
        endwhile;
      endif;
      ?>
    </div>
  </div>
</section>