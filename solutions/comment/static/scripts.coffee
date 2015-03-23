$(document).ready ->
  routes = $$.getVar 'solution_comment_routes'
  text = $$.getVar 'solution_comment_text'
  model = $$.getVar 'solution_comment_model'
  loading = false

  query = (controller, data, callback) ->
    if !routes[controller]
      $$.error 'error: can\'t get url'
      return
    $.post routes[controller], data, (response) ->
      if response
        loading = false
        if typeof callback == 'function'
          result = callback response
          response = result if typeof result == 'object'
        if response.message
          $$.solutions.widget.alert response.message
        if response.callback
          $$.executeFunctionByName(response.callback, response.callback_args)

  solution_comment = $('.solutionComment')

  solution_comment.on 'submit', '.add', ->
    return false if loading
    loading = true
    submit_button = $('.solutionComment .add button[type=submit]')
    submit_button.addClass 'btn-loading'
    query 'add', $$.serializeData(this), (response) ->
      if typeof response != 'object'
        response =
          message : response
      submit_button.removeClass 'btn-loading'
      if response['result']
        if response['html']
          $(response['html']).insertAfter $('.solutionComment .add').parent()
        $('.solutionComment .defaultComment a[rel=reply]').removeClass 'hidden'
        $('.solutionComment .add').addClass 'hidden'
      response
    false

  solution_comment.on 'click', '.btn-cancel', ->
    solution_add = $ '.solutionComment .add'
    solution_add.addClass 'hidden'
    solution_add.prev().removeClass 'hidden'
    solution_add.insertAfter solution_comment.find('.top_comment')

  solution_comment.on 'click', 'a[rel=reply]', ->
    solution_add = $ '.solutionComment .add'
    solution_add.removeClass 'hidden'
    solution_add.prev().removeClass 'hidden'
    solution_add.insertAfter this
    solution_add.find('input[name=parent_id]').val($(this).data('id'))
    solution_add.find('textarea').val ''
    $(this).addClass 'hidden'

  solution_comment.on 'click', 'a[rel=delete]', ->
    $$.solutions.widget.confirm text['confirm_delete'], =>
      data =
        comment_id : $(this).data('id')
        vote : $(this).attr('rel')
        solution_comment_model : model.name
        sign : model.sign
      query 'delete', data, (response) =>
        if response['result']
          $(this).parents('.comment:first').remove()
        response

  solution_comment.on 'click', 'a[rel=spam]', ->
    $$.solutions.widget.confirm text['confirm_spam'], =>
      data =
        comment_id : $(this).data('id')
        solution_comment_model : model.name
        sign : model.sign
        type : 'spam'
      query 'complaint', data, (response) ->
        response

  solution_comment.on 'click', 'a[rel=complaint]', ->
    $$.solutions.widget.confirm text['confirm_complaint'], =>
      data =
        comment_id : $(this).data('id')
        solution_comment_model : model.name
        sign : model.sign
        type : 'complaint'
      query 'complaint', data, (response) ->
        response
