$ ->
  header = $('header')
  
  # Determine heights
  header_h = header.outerHeight()
  
  # Apply heights
  $('main').css "margin-top", header_h
  
  $(window).on "resize", ->
    
    # Re-determine heights
    header_h = header.outerHeight()
    
    # Re-apply heights
    $('main').css "margin-top", header_h