<?php
//
//	summary
//		Creates/Opens files for logging data
//		Useful for logging iinformation on a remote server
//		when you don't have access to log files
//		Also helpful for XHRs - since the page doesn't change
//		to the PHP location which normally shows log data
//		or errors.
//
//
 class cLOG {
	var $logfile;
	var $boolTimestamp;

	function __construct($filename, $boolTimestamp){
		$this->boolTimestamp = $boolTimestamp;

		// Validate filename parameter
		if (empty($filename) || $filename === null) {
			// Provide a default filename if none is provided
			$this->logfile = "default_log.txt";
		} else {
			$this->logfile = $filename;
		}
	}

	// Keep the old constructor for backward compatibility
	function cLOG($filename, $boolTimestamp){
		$this->__construct($filename, $boolTimestamp);
	}
	function write($txt){
		// Validate logfile before attempting to open
		if (empty($this->logfile) || $this->logfile === null) {
			error_log("cLOG: Cannot write to log - no valid filename specified");
			return false;
		}

		if($this->boolTimestamp){
			$dt = date("y.m.d G.i.s");
			$txt = "[". $dt ."]: ".$txt;
		}

		$fh = fopen($this->logfile, "a");
		if ($fh === false) {
			error_log("cLOG: Failed to open log file: " . $this->logfile);
			return false;
		}

		if(is_array($txt)){
			//$txt = "::::::::".$txt;
			$ar = $txt;
			$txt = "Array:::::\n";
			foreach($ar as $key => $value){
				if (is_array($value)) {
					$txt .= $key."=Array(...)\n";
				} else {
					$txt .= $key."=".$value."\n";
				}
			}
		}
		fwrite($fh, $txt."\n");
		fclose($fh);
		return true;
	}
	function clear(){
		// Validate logfile before attempting to open
		if (empty($this->logfile) || $this->logfile === null) {
			error_log("cLOG: Cannot clear log - no valid filename specified");
			return false;
		}

		$fh = fopen($this->logfile, "w");
		if ($fh === false) {
			error_log("cLOG: Failed to open log file for clearing: " . $this->logfile);
			return false;
		}

		fwrite($fh, "");
		fclose($fh);
		return true;
	}
	function newline(){
		// Validate logfile before attempting to open
		if (empty($this->logfile) || $this->logfile === null) {
			error_log("cLOG: Cannot add newline to log - no valid filename specified");
			return false;
		}

		$fh = fopen($this->logfile, "a");
		if ($fh === false) {
			error_log("cLOG: Failed to open log file for newline: " . $this->logfile);
			return false;
		}

		fwrite($fh, "\n\n");
		fclose($fh);
		return true;
	}
	function printr($ar){
		$txt = "";
		foreach ($ar as $nm => $val) {
			if (is_array($val)) {
				$txt .= "    ".$nm ." = Array(\n";
				foreach ($val as $subkey => $subval) {
					if (is_array($subval)) {
						$txt .= "        [".$subkey."] = Array(...)\n";
					} else {
						$txt .= "        [".$subkey."] = " . $subval . "\n";
					}
				}
				$txt .= "    )\n";
			} else {
				$txt .= "    ".$nm ." = " . $val . "\n";
			}
		}
		$this->write($txt);
	}
}
?>