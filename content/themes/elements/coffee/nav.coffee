$ ->
  nav = $('header nav')
  open = $('.nav-open')
  close = $('.nav-close')
  header = $('header')
  
  open.click ->
    header.addClass "nav-active"
    nav.addClass "is-active"
    
  close.click ->
    header.removeClass "nav-active"
    nav.removeClass "is-active"