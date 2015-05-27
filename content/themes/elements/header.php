<?php
/**
 * @package WordPress
 */
?>

<!DOCTYPE html>
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
  <title>LCec<?php if(! is_front_page() ){ echo ' – ' . get_the_title(); } ?></title>
  
  <!-- META TAGS -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no">
  
  <!-- LINK tags -->
  <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/app.css">
  
  <!-- Fonts from Typekit -->
  <script src="//use.typekit.net/jif1vmt.js"></script>
  <script>try{Typekit.load();}catch(e){}</script>
  
  <!-- Required for Google Maps -->
  <script src='http://maps.googleapis.com/maps/api/js?sensor=false' type='text/javascript'></script>
  
  <!-- WP_HEAD() -->
  <?php wp_head(); ?>
</head>

<body class="is-loading">
  <!-- Header -->
  <header>
    <a href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/logo.svg"></a>
    
    <nav>
      <?php
      $nav = array(
        'theme_location'  => 'menu_primary',
        'container' => ' ',
        'items_wrap' => '<ul>%3$s</ul>'
      );
    
      wp_nav_menu( $nav );
      ?>
      <!-- <?php do_action('icl_language_selector'); ?> -->
    </nav>
  </header>
  
  <!-- Main content -->
  <main role="main">