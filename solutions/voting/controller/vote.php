<?
namespace solutions\voting;
use solutions\user;
use solutions\voting;

$this->model_name = $this->getCalledData('model_name', true);
$this->can_vote = false;
$this->target_element_id = $this->getCalledData('target_element_id', true);
$this->dependency_entity = $this->getCalledData('dependency_entity', true);

if($this->getCalledData('enabled', true)) {
	if (!voting::_checkAlreadyVoted('+', $this->model_name, $this->target_element_id, $this->dependency_entity)) {
		$this->can_vote = true;
	} else if (!voting::_checkAlreadyVoted('-', $this->model_name, $this->target_element_id, $this->dependency_entity)) {
		$this->can_vote = true;
	}
}

$this->model = new $this->model_name;
$this->model->target_element_id = $this->target_element_id;
$this->model->dependency_entity = $this->dependency_entity;
$this->model->find();
