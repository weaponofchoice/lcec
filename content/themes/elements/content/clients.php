<section class="section_clients">
  <div class="section_body">
    <ul class="s-grid-2 m-grid-4">
      <?php
      if( have_rows('clients') ):
        while( have_rows('clients') ): the_row();
          
          $group = get_sub_field( 'clients_group' );
          
          echo '<li><img src="' . $group['sizes']['large'] . '"></li>';
          
        endwhile;
      endif;
      ?>
    </ul>
  </div>
</section>