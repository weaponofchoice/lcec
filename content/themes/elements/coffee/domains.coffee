$ ->
  # Variables
  domain = $('.section_domain')
  top = $('.section_domain .top')
  bottom = $('.section_domain .bottom')
  
  # Determine height(s)
  viewport_h = window.innerHeight
  header_h = 60
  
  # Apply heights
  domain.css "height", viewport_h - header_h
  top.css "height", viewport_h - header_h
  bottom.css "height", viewport_h - header_h
  
  # Fade in
  waypoints = domain.waypoint(((direction) ->
    $(this.element).toggleClass "test"
  ), offset: header_h * 3 )
  
  # Fade out
  waypoints = domain.waypoint(((direction) ->
    $(this.element).toggleClass "test"
  ), offset: '-70%' )
  
  # resizing window
  window.on 'resize', ->
    # Determine height(s)
    viewport_h = window.innerHeight
    header_h = 60
  
    # Apply heights
    domain.css "height", viewport_h - header_h
    top.css "height", viewport_h - header_h
    bottom.css "height", viewport_h - header_h
  
    # Fade in
    waypoints = domain.waypoint(((direction) ->
      $(this.element).toggleClass "test"
    ), offset: header_h * 3 )
  
    # Fade out
    waypoints = domain.waypoint(((direction) ->
      $(this.element).toggleClass "test"
    ), offset: '-70%' )