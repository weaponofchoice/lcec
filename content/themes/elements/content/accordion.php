<section class="section_accordion">
  <div class="section_header">
    <?php
    $items = get_sub_field( 'accordion_index' );
    
    if( $items ): $i = 1;
      foreach( $items as $item ):
        $item_image = $item['accordion_c_single_icon'];
        $item_title = $item['accordion_c_single_title'];
        ?>
        <a data-count="<?php echo $i; ?>">
          <img src="<?php echo $item_image['url']; ?>">
          <p><?php echo $item_title; ?></p>
        </a>
        <?php
        $i++;
      endforeach; 
    endif;
    ?>
  </div>
  
  <div class="section_body">
    <?php
    $items = get_sub_field( 'accordion_index' );
    
    if( $items ): $i = 1;
      foreach( $items as $item ):
        $item_text = $item['accordion_c_single_text'];
        ?>
        <div class="<?php echo $i; ?>" data-count="<?php echo $i; ?>">
          <?php echo $item_text; ?>
        </div>
        <?php
        $i++;
      endforeach; 
    endif;
    ?>
  </div>
</section>