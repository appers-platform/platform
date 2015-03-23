<?
interface backgroundDriver {
	static public function instance();
	public function addTask( $task, $data );
	public function addTaskLow( $task, $data );
	public function addTaskHigh( $task, $data );
	public function listen($task, $callback);
}
