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
  
  <link rel="canonical" href="<?php echo home_url(); ?>">
  
  <!-- META TAGS -->
  <meta name="description" content="LC Engineers and Consultants" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/app.css">
  
  <!-- Favicons -->
  <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/img/favicon-16x16.png" sizes="16x16">
  <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/img/favicon-32x32.png" sizes="32x32">
  
  <!-- Fonts from Typekit -->
  <script src="//use.typekit.net/jif1vmt.js"></script>
  <script>try{Typekit.load();}catch(e){}</script>
  
  <!-- Required for Google Maps -->
  <script src='http://maps.googleapis.com/maps/api/js?sensor=false' type='text/javascript'></script>
  
  <!-- WP_HEAD() -->
  <?php wp_head(); ?>
</head>

<body <?php body_class('is-loading'); ?>>
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
      include_once('includes/lang.php');
      ?>
      <div class="address">
        <p>+31 168 382 411</p>
        <p>Plaza 8b<br>4782 SK Moerdijk<br>Nederland</p>
        <a class="is_bold" href="http://maps.google.com/maps?hl=nl&expIds=25657&xhr=t&cp=11&wrapid=tljp1293642618351013&um=1&ie=UTF-8&q=lc+moerdijk&fb=1&hq=lc&hnear=Moerdijk,+Nederland&cid=0,0,9635813263473567923&ei=g2sbTaWeK4vsOaW6rOAI&sa=X&oi=local_result&ct=image&resnum=2&sqi=2&ved=0CCMQnwIwAQ">Zoek route</a>
      </div>
    </nav>
    
    <a class="nav-open"><img src="<?php echo get_template_directory_uri(); ?>/img/nav-open.svg"></a>
    <a class="nav-close"><img src="<?php echo get_template_directory_uri(); ?>/img/nav-close.svg"></a>
  </header>
  
  <!-- Main content -->
  <main role="main">