<?
$task_name = cli::getArgument('name');
$project_name = cli::getArgument('project');
$global = cli::hasArgument('global');

if(!$project_name) $project_name = PROJECT;

if(!$task_name || (!$project_name && !$global)) {
?>
For current project:
./exec app::generateTask name=task_name

For custom project:
./exec app::generateTask name=task_name project=your_project

For global:
./exec app::generateTask name=task_name global
<?
return;
}

if($project_name) {
	$target = PROJECTS_ROOT.'/'.$project_name;
	if(!is_dir($target)) {
		print "Can't find project '{$project_name}'\n";
		return;
	}
} else {
	$target = ROOT;
}

$target .= '/tasks';

if(!is_dir($target))
	throw new Exception("Dir {$target} not found.");

$target .= '/'.str_replace('::', '/', $task_name).'.php';

if(file_exists($target)) {
	print ($global ? 'Global task' : 'Task of project '.$project_name)." '{$task_name}' already exist.\n";
	return;
}

if(file_put_contents($target, "<?\n") === false) {
	print "Oops, error writing to file '{$target}'.\n";
	return;
}

print "Done\n";
