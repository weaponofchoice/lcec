$ ->
  # domains
  domain = $('.section_domain')
  bottom = $('.section_domain .bottom')
  
  Pace.on 'done', ->
    waypoints = domain.waypoint((direction) ->
      top = $(this.element).children('.top')
      header = $(this.element).children('.section_header')
      top.toggleClass "is_pinned"
      header.toggleClass "is_pinned"
    )
    
    waypoints = bottom.waypoint((direction) ->
      $(this.element).toggleClass "is_pinned"
    )