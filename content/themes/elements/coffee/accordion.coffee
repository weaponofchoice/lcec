if $('.section_accordion').length > 0
  tab = $('.section_accordion .section_header > a')
  content = $('.section_accordion .section_body > div')
  container = $('.section_accordion .section_body')
  
  delay = (ms, func) -> setTimeout func, ms
  
  tab.click ->
    tab_count = $(this).attr "data-count"
    match = $('.section_accordion .section_body > div.' + tab_count)
    
    # Checking
    check1 = $(this).hasClass "active"
    check2 = container.hasClass "active"
    
    if check1 and check2 is true
      match.removeClass "active"
      delay 500, -> container.removeClass "active"
      delay 500, -> tab.removeClass "active"
    else
      # Remove other active classes
      tab.removeClass "active"
      content.removeClass "active"
    
      # Give active classes
      container.addClass "active"
      $(this).addClass "active"
      
      delay 500, -> match.addClass "active"