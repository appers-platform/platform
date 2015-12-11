<?
// пример контроллера index

class index_controller extends controller {
	public function first() {
		$this->title = 'Welcome to Appers!';
	}
}

/*
еще он мог выглядеть так:
<?
$this->title = 'Welcome to Appers!';
*/

/*
А еще этот файл можно не создавать в принципе, если он не нужен.
Наличия представления для контроллера хватит для его динамического виртуального создания.
*/
