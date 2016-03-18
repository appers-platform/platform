<?
namespace solutions\user;
use cli;

$command = false;
foreach(['list', 'edit', 'add', 'delete', 'find', 'show_data', 'edit_data'] as $cmd) {
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
	show_data id=000
	edit_data id=000 variable=value
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
		$model = new userModel();
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

	case 'edit_data':
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
		
		$i = 0;
		$data = unserialize($model->data);
		foreach(cli::getArguments() as $key => $value) {
			if(in_array($key, ['id', 'edit_data'])) continue;
			if($data[$key] != $value) {
				$data[$key] = $value;
				$i ++;
			}
		}
		if($i) {
			$model->data = serialize($data);
			$model->store();
		}

		break;

	case 'show_data':
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
		var_dump(unserialize($model->data));
		
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