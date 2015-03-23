<?
namespace solutions\charts;

class simple extends parentChart {
	static protected $counter;

	protected $name = '';
	protected $data = [];
	protected $type = 'LineChart';
	protected $i;
	protected $round = false;
	protected $incremental = false;
	protected $additional_html = '';
	protected $add_table = false;

	public $properties = [
		'width' => 790,
		'height' => 225,
		'legend' => 'none',
		'chartArea' => [ 'width' => 710 ],
		'vAxis' => [ 'baseline' => 0 ],
	];

	public function setAddTable($flag = true) {
		$this->add_table = (bool) $flag;
	}

	public function setIncremental($flag = true) {
		$this->incremental = (bool) $flag;
	}

	public function setAdditionalHtml($html) {
		$this->additional_html = $html;
	}

	public function __construct($name = '') {
		$this->name = $name;
		$this->i = ++self::$counter;
	}

	public function setTypeLineChart() {
		$this->type = 'LineChart';
		$this->properties['isStacked'] = false;
	}

	public function setTypeTimeLine() {
		$this->type = 'AnnotatedTimeLine';
		$this->properties['isStacked'] = false;
	}

	public function setTypeAreaChart() {
		$this->type = 'AreaChart';
		$this->properties['isStacked'] = true;
	}

	public function setTypeNoStackedAreaChart() {
		$this->type = 'AreaChart';
		$this->properties['isStacked'] = false;
	}

	public function setTypeSteppedAreaChart() {
		$this->type = 'SteppedAreaChart';
		$this->properties['isStacked'] = true;
	}

	public function setData($label, array $data, $round = false) {
		$this->data[$label] = $data;
		if($round !== false) {
			foreach($this->data[$label] as $kr => $row) {
				$i = 0;
				foreach($row as $k => $v) {
					if(!$i++) continue;
					$this->data[$label][$kr][$k] = round($v, $round);
				}
			}
		}
	}

	public function setDataFromQuery($label, array $data, $round = false) {
		$prepared = [];
		foreach($data as $row) {
			$prepared[array_shift($row)] = array_shift($row);
		}
		$this->setData($label, $prepared, $round);
	}

	public function setRound($round) {
		$this->round = $round;
	}

	public function __toString() {
		ob_start();
		try {
			$this->draw();
		} catch (\Exception $e) {
			print_r($e);
		}
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	public function getData(&$result = null) {
		$keys = [];
		$f = [];
		foreach($this->data as $data) {
			$keys = array_unique(array_merge($keys, array_keys($data)));
		}

		if(strtotime($keys[0])) {
			$tmp = [];
			foreach($keys as $k) {
				$tmp[strtotime($k)] = $k;
			}
			ksort($tmp);
			$keys = (array_values($tmp));
		} else {
			ksort($keys);
			$keys = array_reverse($keys);
		}

		$result = [[$this->name]];

		foreach(array_keys($this->data) as $title) {
			$result[0][] = $title;
		}

		foreach($keys as $i => $key) {
			$result[$i + 1] = [$key];

			foreach($this->data as $label => $data) {
				$r = ($data[$key] === null) ? null : ((double)$data[$key]);
				if(!isset($f[$label])) {
					if($r === null)
						$r = 0;
					$f[$label] = true;
				}
				if($this->round !== false)
					$r = round($r, $this->round);

				$result[$i + 1][] = $r;
			}
		}

		if($this->incremental) {
			$inc_val = [];
			foreach($result as $row_id => $row) {
				if(!$row_id) continue;
				foreach($row as $col_id => $value) {
					if(!$col_id) continue;
					if($value === null) continue;
					$result[$row_id][$col_id] = $value + $inc_val[$col_id];
					$inc_val[$col_id] += $value;
				}
			}
		}

		return json_encode($result);
	}

	protected function draw() {
		\js::addUrl('https://www.google.com/jsapi', \js::GROUP_SOLUTIONS);
		?>
		<div class="solution chart simple">
			<? if($this->name) echo '<h4>'.htmlspecialchars($this->name).'</h4>'; ?>
			<? if($this->additional_html) echo $this->additional_html; ?>
			<div id="chart-<?=$this->i?>" class="chart-holder"></div>
		</div>
		<script type="text/javascript">
			(function(){
				var draw = function() {
					var data = google.visualization.arrayToDataTable(<?=$this->getData()?>);
					var chart = new google.visualization.<?=$this->type?>(document.getElementById('chart-<?=$this->i?>'));
					var properties = <?=json_encode($this->properties)?>;
					chart.draw(data, properties);
				};
				if(!google.visualization) {
					google.load("visualization", "1", {packages:["corechart", "table"]});
					google.setOnLoadCallback(draw);
				} else {
					draw();
				}
			})();
		</script>
	<?
	}

	public function calcDelta() {
		$data = null;
		$this->getData($data);
		foreach($data as $row) {
			if($row[2] === null) break;
			$last_row = $row;
		}
		list(,$yesterday) = array_pop($data);
		$delta = $last_row[2] - $last_row[1];
		$html = '<b>Yesterday:</b> '.$last_row[1].' / '.$yesterday.' | <b>Today:</b> '.$last_row[2].' &nbsp;|&nbsp;';

		$html .= "<span style=\"color:".($delta < 0 ? 'red' : ($delta > 0 ? 'green' : 'blue'))."\">";
		$html .= $delta > 0 ? "+$delta" : $delta;

		$html .= ' ';
		if($tvd = ($last_row[2] - $delta)) {
			$p_delta = round($last_row[2]/$tvd*100 - 100);
			$html .= ($p_delta > 0 ? '+'.$p_delta : $p_delta).'%';
		} else {
			$html .= '0%';
		}

		$html .= '</span>';

		$this->setAdditionalHtml($html);
	}

	public function setRows(array $rows, $round = null) {
		$result = [];

		foreach($rows as $row) {
			$i = 0;
			$k = '';
			foreach($row as $c_title => $c_value) {
				if(!$i++) {
					$k = $c_value;
					continue;
				}
				if(!isset($result[$c_title]))
					$result[$c_title] = [];

				$result[$c_title][$k] = ($round === null) ? $c_value : round($c_value, $round);
			}
		}

		foreach($result as $label => $data) {
			$this->setData($label, $data);
		}
	}
}
