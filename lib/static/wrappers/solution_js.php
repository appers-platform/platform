$$.registerSolutionScript(<?=json_encode($solution_name)?>, function($$data, __solution_data_sign){
var $$ajax = $$.getSolutionAjaxFunction($$data, __solution_data_sign);
$$data = eval ("(" + $$data + ")");
<?=$content?>
});
