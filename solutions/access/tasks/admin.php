<?
namespace solutions\access;
use \solutions\access;
use \cli;
use \solutions\user\userModel;

if(!PROJECT) {
	print "Task should be runned from project\n";
	return ;
}

$command = false;
foreach(['list', 'show', 'edit', 'find'] as $cmd) {
	if(cli::hasArgument($cmd)) {
		$command = $cmd;
		break;
	}
}

if(!$command) {
?>


Options:
	list [access=type] [limit=50] - list all users with access (limit 50 by default)
	find email=mail@examp.com [limit=50]
	show user_id=000
	edit user_id=000 [add=type[,type2]]
	edit user_id=000 [delete=type[,type2]]

<?
	return;
}

switch($command) {
	case 'list':
		print "\n";
		$model = new accessModel();

		if($access = cli::getArgument('access')) {
			$model->access_list = [ 'LIKE', '%"'.$access.'"%' ];
		}
		$model->access_list = [ '!=', 'a:0:{}' ];

		foreach($model->findAll(null, (int) cli::getArgument('limit', 50)) as $access_model) {
			print $access_model->user_id;
			print "\t";
			$user_model = new userModel($access_model->user_id);
			if($user_model->getId()) {
				print $user_model->email;
			} else {
				print '(deleted)';
			}
			print "\t";
			print implode(',', access::getAccess($user_model));
			print "\n";
		}
		break;

	case 'show':
		$user_model = new userModel();
		if(!$user_model->id = cli::getArgument('user_id')) {
			print "You should enter user_id\n";
			return;
		}
		$user_model->find();
		if(!$user_model->getId()) {
			print "User not found\n";
			return;
		}

		print implode(',', access::getAccess($user_model));

		break;

	case 'find':
		$user_model = new userModel();
		if(!$user_model->email = ['like', '%'.cli::getArgument('email').'%']) {
			print "You should enter email\n";
			return;
		}
		$users = $user_model->findAll(null, (int) cli::getArgument('limit', 50));
		if(!count($users)) {
			print "Not found.\n";
			return;
		}
		foreach ($users as $user) {
			print $user->id;
			print "\t";
			print $user->email;
			print "\t";
			print implode(',', access::getAccess($user));
			print "\n";
		}
		break;

	case 'edit':
		$user_model = new userModel();
		if(!$user_model->id = cli::getArgument('user_id')) {
			print "You should enter user_id\n";
			return;
		}
		$user_model->find();
		if(!$user_model->getId()) {
			print "User not found\n";
			return;
		}

		if($add = cli::getArgument('add')) {
			foreach(explode(',', $add) as $access) {
				access::addUserAccess($access, $user_model);
			}
		}

		if($delete = cli::getArgument('delete')) {
			foreach(explode(',', $delete) as $access) {
				access::removeUserAccess($access, $user_model);
			}
		}

		print "Done, new access of user: ".implode(',', access::getAccess($user_model));

		break;
}
