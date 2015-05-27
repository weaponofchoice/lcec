$ ->
  header = $('header')
  scrolled = $(window).scrollTop()
  
  if scrolled > 120
    header.addClass "shrunk"
  else
    header.removeClass "shrunk"
  
  $(window).scroll ->
    header = $('header')
    scrolled = $(this).scrollTop()
    
    if scrolled > 120
      header.addClass "shrunk"
    else
      header.removeClass "shrunk"