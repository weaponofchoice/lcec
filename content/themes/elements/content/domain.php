<?php
$domain_c_background = get_sub_field( 'domain_c_background' );
$domain_c_caption = get_sub_field( 'domain_c_caption' );
$domain_c_title = get_sub_field( 'domain_c_title' );
$domain_c_text = get_sub_field( 'domain_c_text' );
?>
<section class="section_domain" id="anchor-<?php echo $i_anchor; ?>">
  <div class="section_header">
    <p><?php echo $domain_c_caption; ?></p>
    <h2><?php echo $domain_c_title; ?></h2>
  </div>
  
  <div class="top" style="background-image:url('<?php echo $domain_c_background['sizes']['large']; ?>')"></div>
  
  <div class="bottom">
    <div>
      <?php echo $domain_c_text; ?>
      
      <?php
      if( have_rows('domain_c_links') ):
        while( have_rows('domain_c_links') ): the_row();
          $domain_c_link = get_sub_field( 'domain_c_links_link' );
          $domain_c_link_text = get_sub_field( 'domain_c_links_text' );
          ?>
          <a class="link-arrow is_bold" href="<?php echo $domain_c_link; ?>"><?php echo $domain_c_link_text; ?><img src="<?php echo get_template_directory_uri(); ?>/img/arrow-pri.svg"></a>
          <?php
        endwhile;
      endif;
      ?>
    </div>
  </div>
</section>