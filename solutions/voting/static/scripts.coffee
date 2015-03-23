# solution_ajax
# solution_data

if !$$data
  return

class_name = 'voting_' + $$data.target_element_id + '_' + $$data.dependency_entity

$('.votingSolution.'+class_name+' a').click ->
  data =
    vote : $(this).attr('rel')
  $$ajax $$data.vote_url, data, (response) =>
    if response.message
      $$.solutions.widget.alert response.message
    if response.callback
      $$.executeFunctionByName(response.callback, response.callback_args)
    if response['result']
      text = ''
      val_node = $(this).parent().find('.value')
      if response['rate'] > 0
        text = '+'
        val_node.removeClass('minus').addClass('plus')
      else if response['rate'] < 0
        val_node.removeClass('plus').addClass('minus')
      else
        val_node.removeClass('plus').removeClass('minus')
      text += response['rate'].toString()
      val_node.text(text)
    true



