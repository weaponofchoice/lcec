Pace.on 'done', ->
  image = $('.retina')
  
  if window.devicePixelRatio > 1
    image.each ->
      src = $(this).css('background-image')
      src_stripped = src.replace('url(','').replace('.jpg)','');
      $(this).css "background-image", "url('" + src_stripped + "@2x.jpg')"
  else
    image.removeClass "retina"