Pace.on 'done', ->
  # Variables
  domain = $('.section_domain')
  top = $('.section_domain .top')
  bottom = $('.section_domain .bottom')
  
  # Determine height(s)
  viewport_h = window.innerHeight
  header_h = 60
  
  # Fade in
  waypoints = domain.waypoint(((direction) ->
    $(this.element).toggleClass "activated"
  ), offset: 200 )
  
  # resizing window
  $(window).on "resize", ->
    # Determine height(s)
    viewport_h = window.innerHeight
    header_h = 60
  
    # Fade in
    waypoints = domain.waypoint(((direction) ->
      $(this.element).toggleClass "activated"
    ), offset: 200 )