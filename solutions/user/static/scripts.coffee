
$$.registerSolution 'user',
  showLoginForm : (success_url) ->
    data =
      afterAuthUrl : success_url || ''
    $$.solutions.widget.frame '/user/login?_frame=1&' + $$.serializeObject(data)
  showRegisterForm : (success_url) ->
    data =
      afterAuthUrl : success_url || ''
    $$.solutions.widget.frame '/user/registration?_frame=1&' + $$.serializeObject(data)
