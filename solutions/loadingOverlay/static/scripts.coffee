$(document).ready ->
  overlay = $("<div></div>").attr 'id', 'solution_loadingOverlay'
  overlay.appendTo $("body")
  $(window).unload ->
    overlay.css 'display', 'block'
  $(document).click (event) ->
    if event.target.nodeName.toUpperCase() == 'A'
        overlay.css 'display', 'block'
        setTimeout -> 
            document.location.href = $(event.target).attr('href')
        , 100
        return false