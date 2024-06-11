<?
class Debug {
	static public function vd($inArray, $title = "", $display = true) {
		self::print_array($inArray, "var_dump", $title, $display);
	}
	static public function pr($inArray, $title = "", $display = true) {
		self::print_array($inArray, "print_r", $title, $display);
	}
	static private function print_array($inArray, $method, $title = "", $display) {
		if($display == false) {
			echo "<style>";
			echo "pre{display:none}";
			echo "</style>";
		}
		if(!empty($title)) {
			echo "<pre>", "######### " . $title . " #########", "</pre>";
			echo "<pre>", "-----// start //-----", "</pre>";
		}
		echo "<pre>";
		$method($inArray);
		echo "</pre>";
		if(!empty($title)) {
			echo "<pre>", "-----// end //-----", "</pre>";
			echo "<pre><br></pre>";
		}
	}
	static public function prConsole($inArray) {
		echo "<script>console.log(JSON.stringify(" . json_encode($inArray) . ", null, 2));</script>";
	}
	static public function writeLog($data, $filePath, $filename = ""){
		if(empty($filename)) {
			$filename = date("D j d Y | H_i_s");
		}
		if(strpos($filename, ".log") === false) {
			$filename = $filename . ".log";
		}

		$filePath = $_SERVER["DOCUMENT_ROOT"] . $filePath . $filename;
		
		if($file = fopen($filePath, 'a')){
			$data = self::getContetnts($data);
			fwrite($file, $data);
			fclose($file);
			return true;
		} else {
			return false;
		}
	}
	static private function getContetnts($data){
		ob_start();
		echo '-------------------------' . date("F j, Y, g:i a") . '-------------------------', "\n", print_r($data, true), "\n";
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}
?>