<?
$solution_name = cli::getArgument('name');
if(!$solution_name) {
	?>
	Example:
	./exec app::generateSolution name=solution_name
	<?
	return;
}

$target = ROOT.'/solutions';

if(!is_dir($target))
	throw new Exception("Dir {$target} not found.");

$target .= '/'.$solution_name;

if(is_dir($target)) {
	print "Solution '{$solution_name}' already exist.\n";
	return;
}

if(!mkdir($target)) {
	print "Oops, error create directory '{$target}'.\n";
	return;
}

$target .= '/'.$solution_name.'.php';

$code = "<?
namespace solutions;

class {$solution_name} extends solution {
\t
}
";

if(file_put_contents($target, $code) === false) {
	print "Oops, error writing to file '{$target}'.\n";
	return;
}

print "Done\n";
