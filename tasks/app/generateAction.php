<?
if(!PROJECT) {
	print('This task should be launched from Apper');
	return;
}

do {
	$this->out("Enter action name:\n> ", false);
	$name_ok = false;
	if($name = trim($this->getLine())) {
		if(!preg_match("/^[0-9a-z_\\/]+$/i", $name)) {
			print('Name is incorrect');
		} else {
			$path = PROJECT_ROOT.'/controller/'.$name;
			if(is_dir($path) || is_file($path.'.php')) {
				print('Already exist');
			} else {
				$name_ok = true;
			}
		}
	}
} while (!$name_ok);

if(!cli::confirm("Do you really want create action?")) {
	return;
}

$path = explode('/', $path);
if($path[0] == '')
	array_shift($path);
$pass_path = [];
foreach($path as $i => $part_name) {
	$pass_path[] = $part_name;
	if($path_str = implode('/',$pass_path)) {
		if($i != (count($path) - 1)) {
			if(!is_dir($path_str)) {
				mkdir($path_str);
			}
		} else {
			$controller_text = '<?
class '.str_replace('/','_',$name).'_controller extends controller {
	public function first() {

	}
}
';
			file_put_contents($path_str.'.php', $controller_text);
			if(!is_dir($view_dir = dirname($path_str).'/_view')) {
				mkdir($view_dir);
			}

			$last_name = array_pop($path);
			if(!is_file($view_file_name = $view_dir.'/'.$last_name.'.view.php')) {
				file_put_contents($view_file_name, '');
			}

			break;
		}
	}
}

print 'Done';