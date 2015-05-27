$ ->
  header = $('header')
  
  # Determine heights
  header_h = header.outerHeight()
  
  # Apply heights
  
  
  $(window).on "resize", ->
    
    # Re-determine heights
    header_h = header.outerHeight()
    
    # Re-apply heights
    