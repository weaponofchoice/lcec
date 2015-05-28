$ ->
  # Variables
  domain = $('.section_domain')
  top = $('.section_domain .top')
  bottom = $('.section_domain .bottom')
  
  # Determine height(s)
  header_h = 60
  
  Pace.on 'done', ->
    waypoints = domain.waypoint(((direction) ->
      top = $(this.element).children('.top')
      header = $(this.element).children('.section_header')
      
      top.toggleClass "is_pinned"
      header.toggleClass "is_pinned animated"
    ), offset: header_h )
    
    waypoints = bottom.waypoint(((direction) ->
      domain = $(this.element).parent('.section_domain')
      domain.toggleClass "is_scrolled"
    ), offset: header_h )