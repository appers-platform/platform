class Events
  events : {}

  addListener : (name, callback) ->
    if typeof @events[name] != 'object'
      @events[name] = []
    @events[name].push callback

  fireEvent : (name) ->
    if @events[name]
      for callback in @events[name]
        if typeof callback == 'function'
          callback()

  clearEvent : (name) ->
    @events[name] = []

  clear : () ->
    @events = {}

  fireAndClearEvent : (name) ->
    @fireEvent(name)
    @clearEvent(name)

$$.registerLib 'events', Events
