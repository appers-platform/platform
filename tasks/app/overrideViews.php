<?

if(!PROJECT) {
	print "You should run this task only from project.\n";
	return ;
}

if(!($solution = cli::getArgument('solution'))) {
	print "You should set 'solution'.\n";
	return ;
}

if(!is_dir(ROOT.'/solutions/'.$solution)) {
	print "Can't find solution '".$solution."'.\n";
	return ;
}

if(!is_dir($from = ROOT.'/solutions/'.$solution.'/view')) {
	print "Can't find views for solution '".$solution."'.\n";
	return ;
}

solutions\fs::copy($from, PROJECT_ROOT.'/solutions/'.$solution, true);

print "Done.\n";
