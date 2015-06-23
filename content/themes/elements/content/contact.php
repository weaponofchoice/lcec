<?php
$checkbox = get_sub_field( 'form_checkbox' );
if( $checkbox === true ):
?>
  
  <section class="section_contact">
    <div class="section_body">
      <?php
      $lang = get_bloginfo( 'language' );
      if( $lang == 'en-US' ){
        echo do_shortcode('[contact-form-7 id="138" title="General Enquiries"]');
      } else {
        echo do_shortcode('[contact-form-7 id="173" title="Algemene Vragen"]');
      }
      ?>
    </div>
  </section>
  
<?php endif; ?>