Pace.on 'done', ->
  $(window).scroll ->
    header = $('header')
    value = $(this).scrollTop()
    
    if value > 120
      header.addClass "shrunk"
    else
      header.removeClass "shrunk"