<?
function getEntries($php_code) {
	$result = [];
	$tokens = [];
	foreach(token_get_all($php_code) as $token) {
		if(is_array($token) && $token[0] == T_WHITESPACE)
			continue;
		$tokens[] = $token;
	}

	$count = count($tokens);
	for ($i = 3; $i < $count; $i++) {
		if ( 1
			&& is_array($tokens[$i - 3] ) && $tokens[$i - 3][0] == 307 && $tokens[$i - 3][1] == '__'
										  && $tokens[$i - 2] == '('
			&& is_array($tokens[$i - 1] ) && $tokens[$i - 1][0] == 315
										  && ($tokens[$i] == ',' || $tokens[$i] == ')')
		) {
			$string = substr($tokens[$i-1][1], 1, strlen($tokens[$i-1][1]) - 2);
			if(substr($tokens[$i-1][1], 0, 1) == '\'') {
				$string = str_replace('\\\'', '\'', $string);
			} else if(substr($tokens[$i-1][1], 0, 1) == '"') {
				$string = str_replace('\\"', '"', $string);
			}
			$result[] = $string;
		}
	}

	return $result;
}

if((!cli::hasArgument('project') && !cli::hasArgument('solution')) || !cli::hasArgument('locale')) {
?>
For compile current project:
	./exec app::i18nGenerate locale=de project

For compile custom project:
	./exec app::i18nGenerate locale=de project=your_project

For compile solution:
	./exec app::i18nGenerate locale=de solution=your_solution

For compile/copy solution into current project:
	./exec app::i18nGenerate locale=de solution=your_solution project

For compile/copy solution into project:
	./exec app::i18nGenerate locale=de solution=your_solution project=your_project

PS.: If you want to remove unused phrases, set param 'clear':
	./exec app::i18nGenerate locale=de solution=your_solution project=your_project clear

P.P.S.: If you want compile orig locale, use param 'orig':
	./exec app::i18nGenerate locale=de solution=your_solution orig
<?
	return;
}

$target = null;
$result_target = null;
$out = null;
if ($solution = cli::getArgument('solution')) {
	$target = ROOT.'/solutions/'.$solution;
	if(!is_dir($target)) {
		print "Target solution '{$solution}' not found.";
		return;
	}
	if(cli::hasArgument('project')) {
		if($project = cli::getArgument('project')) {
			$result_target = dirname(ROOT).'/appers/'.$project;
			if(!is_dir($result_target)) {
				print "Target project '{$project}' not found.";
				return;
			}
		} else {
			$result_target = PROJECT_ROOT;
		}
		$result_target .= '/solutions';
		if(!is_dir($result_target))
			if(!mkdir($result_target))
				throw new Exception('Can\'t create directory "'.$result_target.'"');
		$result_target .= '/'.$solution;
		if(!is_dir($result_target))
			if(!mkdir($result_target))
				throw new Exception('Can\'t create directory "'.$result_target.'"');
	}
} else if(cli::hasArgument('project')) {
	if($project = cli::getArgument('project')) {
		$target = dirname(ROOT).'/appers/'.$project;
		if(!is_dir($target)) {
			print "Target project '{$project}' not found.";
			return;
		}
	} else {
		$target = PROJECT_ROOT;
	}
} else {
	print "Error";
	return;
}

if(!$result_target)
	$result_target = $target.'/i18n';

$file_info = new SplFileInfo($target);
$ri = new \RecursiveIteratorIterator(
	new \RecursiveDirectoryIterator($file_info->getRealPath()),
	\RecursiveIteratorIterator::SELF_FIRST
);

$entries = [];
$ext_lib_len = strlen(ROOT.'/extLib/');
foreach ($ri as $file) {
	if(str_replace('\\', '/', substr($file, 0, $ext_lib_len)) == ROOT.'/extLib/') {
		continue;
	}
	if ($file->getExtension() != 'php') {
		continue;
	}
	$files_list[] = str_replace('\\', '/', $file->getPathname());
	$entries = array_merge(
		$entries,
		getEntries( file_get_contents(str_replace('\\', '/', $file->getPathname())) )
	);
}

$entries = array_unique($entries);
$translates = [];
foreach($entries as $entry) {
	$translates[$entry] = $entry;
}

$locale = cli::getArgument('locale');
$filename = cli::hasArgument('orig') ? 'orig' : $locale;

$result['locale'] = $locale;
$result['translates'] = $translates;

if(!is_dir($result_target))
	mkdir($result_target);

$old_data = [];
if(is_file($file_path = $result_target.'/'.$filename.'.yaml')) {
	$old_data = yaml::parseFile($file_path);
	if($result['locale'] == $old_data['locale']) {
		if(cli::hasArgument('clear')) {
			foreach($old_data['translates'] as $phrase => $translate) {
				if($translate && $result['translates'][$phrase])
					$result['translates'][$phrase] = $translate;
			}
		} else {
			$translates = $old_data['translates'];
			foreach(array_keys($result['translates']) as $phrase) {
				if(!$translates[$phrase]) {
					$translates[$phrase] = $phrase;
				}
			}
			$result['translates'] = $translates;
		}
	}
}

file_put_contents( $file_path, yaml::dump($result) );

print "Done";
