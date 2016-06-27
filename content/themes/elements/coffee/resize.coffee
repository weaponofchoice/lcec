body = $('body')
resizeTimer = undefined

$(window).resize ->
  body.addClass "is-resizing"

$(window).on 'resize', (e) ->
  clearTimeout resizeTimer

  resizeTimer = setTimeout((->
    body.removeClass "is-resizing"
    return
  ), 500)
  return