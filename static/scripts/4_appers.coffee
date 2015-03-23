$$.log = ->
  try
    console.log.apply console, arguments
  catch error

$$.error = ->
  try
    console.error.apply console, arguments
  catch error

$$.registerSolution = (name, solution) ->
  $$.solutions[name] = solution

$$.registerSolutionScript = (name, script) ->
  if !($$.solutions_scripts[name] instanceof Array)
    $$.solutions_scripts[name] = []
  $$.solutions_scripts[name].push script

$$.callSolutionScripts = (name, data, data_sign) ->
  if $$.solutions_scripts[name] instanceof Array
    for script in $$.solutions_scripts[name]
      script(data, data_sign)

$$.registerPattern = (name, pattern) ->
  $$.patterns[name] = pattern

$$.registerLib = (name, lib) ->
  $$.libs[name] = lib

$$.serializeData = (element) ->
  result = {}
  $.each $(element).serializeArray(), (k, value) ->
    result[value.name] = value.value
  result

$$.getSolutionAjaxFunction = (solution_data, solution_data_sign) ->
  ((solution_data, solution_data_sign) ->
    ->
      args = arguments
      if args.length < 2
        args[1] = {}
        args.length = 2
      args[1]['solution_data'] = solution_data
      args[1]['solution_data_sign'] = solution_data_sign

      $.post.apply $, args
  ) solution_data, solution_data_sign

$$.ready = (callback) ->
  console.log($$.solutions)
  $(document).ready callback