$ ->
  Pace.on 'done', ->
    $('body').removeClass "is-loading"
    
    # Animations
    delay = (ms, func) -> setTimeout func, ms
    
    intro1 = $('.section_grid ul li:nth-child(1)')
    intro2 = $('.section_grid ul li:nth-child(2)')
    intro3 = $('.section_grid ul li:nth-child(3)')
    intro4 = $('.section_grid ul li:nth-child(4)')
    intro5 = $('.section_grid .section_header')
    text = $('.section_text')
    
    intro1.addClass "animated"
    delay 150, -> intro2.addClass "animated"
    delay 300, -> intro3.addClass "animated"
    delay 450, -> intro4.addClass "animated"
    delay 750, -> intro5.addClass "animated"
    delay 1050, -> text.addClass "animated"
    
    # Remove pace elements when done
    $('.pace').remove()
    return