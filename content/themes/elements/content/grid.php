<?php
$grid_title = get_sub_field( 'grid_title' );
$grid_item = get_sub_field( 'grid_item' );

// Output
echo '<section class="section_grid has_no-padTop">';
  
  // Grid header
  if( $grid_title ):
    echo '<div class="section_header row fadeIn-up">';
      echo '<h2 class="s-4 columns is_white">' . $grid_title . '</h2>';
    echo '</div>';
  endif;
  
  // Grid content
  if( have_rows('grid_item') ):
    echo '<div class="section_body">';
      echo '<ul class="s-grid-1 m-grid-2 l-grid-4">';
        
        while( have_rows('grid_item') ): the_row();
          $image = get_sub_field( 'grid_item_image' );
          $title = get_sub_field( 'grid_item_title' );
          ?>
          <li class="fadeIn-up" style="background:url('<?php echo $image['sizes']['medium']; ?>')">
            <div class="mask"></div>
            <p class="is_white is_aligned-center"><?php echo $title; ?></p>
          </li>
          <?php
        endwhile;
        
      echo '</ul>';
    echo '</div>';
  endif;
  
echo '</section>';
?>