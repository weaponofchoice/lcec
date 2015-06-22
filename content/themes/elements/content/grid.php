<?php
$grid_title = get_sub_field( 'grid_title' );
$grid_item = get_sub_field( 'grid_item' );

// Output
echo '<section class="section_grid has_no-pad">';
  
  // Grid header
  if( $grid_title ):
    echo '<div class="section_header row fadeIn-up">';
      echo '<h2 class="s-4 columns is_white is_aligned-center">' . $grid_title . '</h2>';
    echo '</div>';
  endif;
  
  // Grid content
  if( have_rows('grid_item') ):
    $i = 1;
    
    echo '<div class="section_body">';
      echo '<ul class="s-grid-2 l-grid-4">';
        
        while( have_rows('grid_item') ): the_row();
          $image = get_sub_field( 'grid_item_image' );
          $title = get_sub_field( 'grid_item_title' );
          ?>
          <li style="background-image:url('<?php echo $image['sizes']['medium']; ?>')">
            <a href="#anchor-<?php echo $i; ?>">
              <div class="mask"></div>
            
              <?php if( $title ): ?>
                <p class="is_white is_aligned-center fadeIn-up"><?php echo $title; ?></p>
              <?php endif; ?>
            </a>
          </li>
          <?php
          $i++;
        endwhile;
        
      echo '</ul>';
    echo '</div>';
  endif;
  
echo '</section>';
?>