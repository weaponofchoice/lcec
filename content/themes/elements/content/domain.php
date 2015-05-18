<?php
$domain_c_caption = get_sub_field( 'domain_c_caption' );
$domain_c_title = get_sub_field( 'domain_c_title' );
$domain_c_background = get_sub_field( 'domain_c_background' );
$domain_c_text = get_sub_field( 'domain_c_text' );
$domain_c_link = get_sub_field( 'domain_c_link' );
$domain_c_link_text = get_sub_field( 'domain_c_link_text' );
?>
<section class="section_domain">
  <div class="section_header fadeIn-left">
    <p><?php echo $domain_c_caption; ?></p>
    <h2><?php echo $domain_c_title; ?></h2>
  </div>
  
  <div class="top" style="background:url('<?php echo $domain_c_background['sizes']['large']; ?>')">
    
  </div>
  
  <div class="bottom">
    <div>
      <?php echo $domain_c_text; ?>
    </div>
  </div>
</section>