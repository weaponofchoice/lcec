<div class="lang">
  <?php
  $languages = pll_the_languages(array('raw' => 1));
  $lang_current = get_bloginfo('language');
  $i = 1;
  
  foreach ($languages as $lang) {
    // With forward slash '/'
    if( $i == 1 ):
      if( $lang_current == 'en-US' ){
        echo '<a class="trigger trigger-lang link-sec" href ="' . $lang['url'] . '" hreflang = "' . $lang['slug'] . '">' . $lang['name'] . '</a>';
        echo '<span> / </span>';
      } else {
        echo '<a class="trigger trigger-lang link-sec current" href ="' . $lang['url'] . '" hreflang = "' . $lang['slug'] . '">' . $lang['name'] . '</a>';
        echo '<span> / </span>';
      }
    
    // Without forward slash
    else:
      if( $lang_current == 'nl-NL' ){
        echo '<a class="trigger trigger-lang link-sec" href ="' . $lang['url'] . '" hreflang = "' . $lang['slug'] . '">' . $lang['name'] . '</a>';
      } else {
        echo '<a class="trigger trigger-lang link-sec current" href ="' . $lang['url'] . '" hreflang = "' . $lang['slug'] . '">' . $lang['name'] . '</a>';
      }
    endif;
    
    $i++;
  } ?>
</div>