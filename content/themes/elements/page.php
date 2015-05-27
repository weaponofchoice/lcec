<?php
get_header();

// Loop into ACF groups
if( have_rows('page') ):
  while( have_rows('page') ): the_row();
    
    // Hero section is placed before main
    
    if( get_row_layout() == 'text' ):
      include( locate_template('content/text.php') );
    elseif( get_row_layout() == 'accordion' ):
      include( locate_template('content/accordion.php') );
    elseif( get_row_layout() == 'domain' ):
      include( locate_template('content/domain.php') );
    elseif( get_row_layout() == 'grid' ):
      include( locate_template('content/grid.php') );
    endif;
    
  endwhile;
endif;

get_footer();
?>