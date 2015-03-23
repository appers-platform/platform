class widget extends $$.patterns.singleton
  id : 0
  html : """
    <div id='solution_widget'>
      <div class='form'>
        <div class="body"></div>
        <span class="close"></span>
        <div class='buttons'></div>
      </div>
    </div>
    <div id="solution_widget_bg" class="widget_bg">&nbsp;</div>
    """
  dom_node : null
  body_overflow : ''
  is_open : false
  events : new $$.libs.events
  closable : true

  constructor: () ->
    $(@dom_node).remove() if @dom_node
    @dom_node = $(this.html)

  updatePosition : () ->
    $dialogForm = @dom_node.find('.form')
    dialogHeight = $dialogForm.outerHeight()
    windowHeight = (if window.parent && window.parent != window then dialogHeight + 200 else $(window).innerHeight())
    if dialogHeight > windowHeight
      dialogHeight = windowHeight
    dialogWidth = $dialogForm.outerWidth()
    $dialogForm.css(
      top: ((windowHeight-dialogHeight) / 2) + 'px',
      marginLeft: -(dialogWidth / 2) + 'px'
    )

  close: () ->
    if !@closable
      return
    @events.fireAndClearEvent 'beforeClose'
    return false if !@is_open
    @dom_node.remove()
    @dom_node = $(this.html)
    $('body').css 'overflow', @body_overflow
    @is_open = false
    @events.fireAndClearEvent 'afterClose'
    @events.clear()

  disableClose : () ->
    @dom_node.find('.close').remove()
    @closable = false

  open: (content, buttons = {}, closable = true, body_class = '') ->
    return false if @is_open
    @is_open = true
    @closable = closable

    # close button
    if !closable
      @dom_node.find('.close').remove()
    else
      @dom_node.find('div.form').click (event) ->
        event.stopPropagation()
      @dom_node.find('.close').click =>
        @close()
      @dom_node.click =>
        @close()

    if typeof content == 'string'
      @dom_node.find('.body').html content
    else
      $(content).appendTo(@dom_node.find('.body'))

    # buttons
    for name of buttons
      dom_button = $('<button></button>')
      dom_button.addClass('btn').addClass('btn-default')
      dom_button.text name
      if typeof buttons[name] == 'function'
        dom_button.click buttons[name]
      dom_button.appendTo @dom_node.find('.buttons')

    # style
    @dom_node.find('.body').addClass body_class
    $(@dom_node[@dom_node.length - 1]).addClass 'loaded' # bullshit, but it works

    # draw
    body = $('body')
    @body_overflow = body.css 'overflow'
    body.css 'overflow', 'hidden'
    @dom_node.appendTo body
    @updatePosition()

    @events.fireAndClearEvent 'afterOpen'
    true

widget_text = $$.getVar 'solution_widget_text'

$$.registerSolution('widget',
  open : (content, buttons, closable) ->
    widget.getInstance().open(content, buttons, closable)

  close : ->
    widget.getInstance().close()

  confirm : (content, button_y_cb = null, button_y = null, button_n = null, button_n_cb = null ) ->
    button_y = widget_text.yes if button_y == null
    button_n = widget_text.no if button_n == null
    args = {}
    args[button_y] = () ->
      widget.getInstance().close()
      if typeof button_y_cb == 'function'
        button_y_cb()
    args[button_n] = if typeof button_n_cb == 'function' then button_n_cb else => widget.getInstance().close()
    widget.getInstance().open content, args, true, 'ta-center'

  alert : (content, button = null) ->
    args = {}
    button = widget_text.ok if button == null
    args[button] = -> widget.getInstance().close()
    widget.getInstance().open content, args, true, 'ta-center'

  frame : (url) ->
    iframe = $ '<iframe>'
    iframe.addClass 'frame'
    iframe.load ->
      iframe.parents('.form').css 'width', '600px'
      iframe.css 'width', 'auto'
      iframe.css 'height', 'auto'

      setTimeout ->
        width = (iframe.contents().width() + 60) + 'px'
        iframe.css 'height', iframe.contents().height() + 'px'
        iframe.css 'width', '100%'
        iframe.parents('.form').css 'width', width
        widget.getInstance().updatePosition()
      , 50

    widget.getInstance().open(iframe)
    iframe.get(0).src = url

  setAfterOpenCallback : (callback) ->
    widget.getInstance().events.addListener 'afterOpen', callback

  setBeforeCloseCallback : (callback) ->
    widget.getInstance().events.addListener 'beforeClose', callback

  setAfterCloseCallback : (callback) ->
    widget.getInstance().events.addListener 'afterClose', callback

  setNoClosable : () ->
    widget.getInstance().disableClose()
)

