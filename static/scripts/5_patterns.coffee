class Singleton
  # We can make private variables!
  instance = null

  # Static singleton retriever/loader
  @getInstance: ->
    if not instance?
      instance = new @

    instance

$$.registerPattern 'singleton', Singleton
