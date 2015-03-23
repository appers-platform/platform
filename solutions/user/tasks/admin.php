<?
namespace solutions\user;
use cli;

$command = false;
foreach(['list', 'edit', 'add', 'delete', 'find'] as $cmd) {
	if(cli::hasArgument($cmd)) {
		$command = $cmd;
		break;
	}
}

if(!$command) {
?>


Options:
	list [limit=50] - list all users (limit 50 by default)
	edit id=000 [email=box@example.com] [password=PWD]
	add email=box@example.com password=PWD [force]
	delete id=000 [force]
	find email=box@example.com
<?
	return;
}

switch($command) {
	case 'list':
		print "\n";
		$model = new userModel();
		foreach($model->findAll(null, (int) cli::getArgument('limit', 50)) as $user_model) {
			print $user_model->getId();
			print "\t";
			print $user_model->email;
			print "\n";
		}
		break;

	case 'find':
		$model = new userModel();
		if(!$model->email = cli::getArgument('email')) {
			print "You should enter email\n";
			return;
		}
		$model->find();
		if(!$model->getId()) {
			print "User not found\n";
			return;
		}
		print "User ID: ".$model->getId()."\n";
		break;

	case 'add':
		if(!($model->email = cli::getArgument('email'))) {
			print "You should enter email\n";
			return;
		}
		if(!($model->password = cli::getArgument('password'))) {
			print "You should enter password\n";
			return;
		}

		if(!cli::hasArgument('force') && !cli::confirm("User with email '{$email}' will be added. Is it ok?")) {
			return;
		}

		$model = new userModel();
		$model->password = md5($password);
		$model->email = $email;
		$model->store();

		$id = $model->getId();
		print "User with ID {$id} has been created.";

		break;

	case 'edit':
		$model = new userModel();
		if(!$model->id = cli::getArgument('id')) {
			print "You should enter id\n";
			return;
		}
		$model->find();
		if(!$model->getId()) {
			print "User not found\n";
			return;
		}
		if($email = cli::getArgument('email')) {
			$model->email = $email;
		}
		if($password = cli::getArgument('password')) {
			$model->password = md5($password);
		}
		$model->store();

		break;

	case 'delete':
		$model = new userModel();
		if(!$model->id = cli::getArgument('id')) {
			print "You should enter id\n";
			return;
		}
		$model->find();
		if(!$model->getId()) {
			print "User not found\n";
			return;
		}

		if(!cli::hasArgument('force') && !cli::confirm("User with id {$model->id} and email '{$model->email}' will be deleted. Is it ok?")) {
			return;
		}

		$id = $model->id;
		$model->delete();
		print "User with ID {$id} has been deleted.";

		break;
}