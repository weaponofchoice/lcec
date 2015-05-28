  </main>
  
  <!-- Footer -->
  <footer>
    <div class="row">
      <div class="s-4 m-2 columns">
        <img src="<?php echo get_template_directory_uri(); ?>/img/logo-sec.svg">
      </div>
    
      <?php
      if( have_rows('footer_text', 'option') ):
        while( have_rows('footer_text', 'option') ): the_row();
          $footer_text = get_sub_field( 'footer_single' );
          ?>
        
          <div class="s-2 m-1 columns">
            <?php echo $footer_text; ?>
          </div>
        
          <?php
        endwhile;
      endif;
      ?>
    </div>
  </footer>
  
  <!-- Scripts -->
  <?php wp_footer(); ?>
</body>
</html>