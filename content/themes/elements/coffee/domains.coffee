$ ->
  # Variables
  domain = $('.section_domain')
  top = $('.section_domain .top')
  bottom = $('.section_domain .bottom')
  header = $('header')
  
  # Determine height
  viewport_h = window.innerHeight
  header_h = header.height()
  
  top.each ->
    $(this).css "height", viewport_h - header_h
    
  bottom.each ->
    $(this).css "height", viewport_h - header_h
  
  Pace.on 'done', ->
    waypoints = domain.waypoint((direction) ->
      top = $(this.element).children('.top')
      header = $(this.element).children('.section_header')
      
      top.toggleClass "is_pinned"
      header.toggleClass "is_pinned animated"
    )
    
    waypoints = bottom.waypoint((direction) ->
      domain = $(this.element).parent('.section_domain')
      domain.toggleClass "is_scrolled"
    )