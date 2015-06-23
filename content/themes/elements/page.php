<?php
get_header();

// Loop into ACF groups
if( have_rows('page') ):
  $i_anchor = 1;
  while( have_rows('page') ): the_row();
    
    if( get_row_layout() == 'text' ):
      include( locate_template('content/text.php') );
    elseif( get_row_layout() == 'accordion' ):
      include( locate_template('content/accordion.php') );
    elseif( get_row_layout() == 'domain' ):
      include( locate_template('content/domain.php') ); $i_anchor++;
    elseif( get_row_layout() == 'grid' ):
      include( locate_template('content/grid.php') );
    elseif( get_row_layout() == 'form' ):
      include( locate_template('content/contact.php') );
    elseif( get_row_layout() == 'team' ):
      include( locate_template('content/team.php') );
    elseif( get_row_layout() == 'location' ):
      include( locate_template('content/location.php') );
    endif;
    
  endwhile;
endif;

if( have_rows('clients') ):
  include( locate_template('content/clients.php') );
endif;

get_footer();
?>