$ ->
  # Variables
  domain = $('.section_domain')
  top = $('.section_domain .top')
  bottom = $('.section_domain .bottom')
  
  # Determine height(s)
  viewport_h = window.innerHeight
  header_h = 60
  
  # Apply heights
  domain.css "height", (viewport_h * 2) - (header_h * 2)
  
  # Stick
  waypoints = domain.waypoint(((direction) ->
    
    top = $(this.element).children('.top')
    header = $(this.element).children('.section_header') 
    
    top.toggleClass "is_pinned"
    header.toggleClass "is_pinned animated"
    
  ), offset: header_h )
  
  # Scroll on
  waypoints = bottom.waypoint(((direction) ->
    domain = $(this.element).parent('.section_domain')
    
    if direction is "down"
      domain.toggleClass "is_scrolled"
      domain.css "height", viewport_h - header_h
      domain.css "margin-top", viewport_h - header_h
    else
      domain.toggleClass "is_scrolled"
      domain.css "height", (viewport_h - header_h) * 2
      domain.css "margin-top", 0
    
  ), offset: header_h )
  
  # retina support
  if window.devicePixelRatio > 1
    image = $('.top')
    image.each ->
      src = $(this).css('background-image')
      src_stripped = src.replace('url(','').replace('.jpg)','');
      $(this).css "background-image", "url('" + src_stripped + "@2x.jpg')"