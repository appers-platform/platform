$(document).ready ->
  overlay = $("<div></div>").attr 'id', 'solution_loadingOverlay'
  overlay.appendTo $("body")
  $(document).click (event) ->
    if $(event.target).hasClass('loadingOverlay')
        overlay.css 'display', 'block'