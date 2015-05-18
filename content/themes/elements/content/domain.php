<?php
$domain_caption = get_sub_field( 'domain_c_caption' );
$domain_title = get_sub_field( 'domain_c_title' );
$domain_background = get_sub_field( 'domain_c_background' );
$domain_text = get_sub_field( 'domain_c_text' );
$domain_link = get_sub_field( 'domain_c_link' );
$domain_link_text = get_sub_field( 'domain_c_link_text' );
?>
<section class="section_domain">
  <div class="section_header fadeIn-left">
    <p><?php echo $domain_caption; ?></p>
    <h2><?php echo $domain_title; ?></h2>
  </div>
  
  <div class="top" style="background:url('<?php echo $domain_background['sizes']['large']; ?>')">
    
  </div>
  
  <div class="bottom">
    <div>
      <?php echo $domain_text; ?>
      <a href="<?php echo $domain_link; ?>"><?php echo $domain_link_text; ?></a>
    </div>
  </div>
</section>