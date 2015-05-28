$ ->
  Pace.on 'done', ->
    $('body').removeClass "is-loading"
    
    # Animations
    delay = (ms, func) -> setTimeout func, ms
    
    # Animated objects
    intro1 = $('.section_grid .section_header')
    intro2 = $('.section_grid ul li p')
    
    # Control animation classes
    intro1.addClass "animated"
    
    x = 150
    intro2.each (index, element) ->
      delay x, -> $(element).addClass "animated"
      x = x + 150
    
    # Remove pace elements when done
    $('.pace').remove()
    return