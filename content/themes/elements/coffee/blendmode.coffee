Pace.on 'done',  ->
  if typeof window.getComputedStyle(document.body).backgroundBlendMode == 'undefined'
    document.documentElement.className += ' no-background-blend-mode'