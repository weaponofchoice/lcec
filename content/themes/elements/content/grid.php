<?php
$grid_title = get_sub_field( 'grid_title' );
$grid_item = get_sub_field( 'grid_item' );

// Output
echo '<section class="section_grid">';
  
  // Grid header
  if( $grid_title ): 
    echo '<div class="section_header row">';
      echo '<h2 class="s-4 columns">' . $grid_title . '</h2>';
    echo '</div>';
  endif;
  
  // Grid content
  if( have_rows('grid_item') ):
    echo '<div class="section_body">';
      echo '<ul class="s-grid-1 m-grid-2 l-grid-4">';
        
        while( have_rows('grid_item') ): the_row();
          $image = get_sub_field( 'grid_item_image' );
          $title = get_sub_field( 'grid_item_title' );
          
          echo '<li>';
            echo '<div class="mask"></div>';
            echo '<img src="' . $image['sizes']['medium'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '">';
            echo '<p>' . $title . '</p>';
          echo '</li>';
        endwhile;
        
      echo '</ul>';
    echo '</div>';
  endif;
  
echo '</section>';
?>