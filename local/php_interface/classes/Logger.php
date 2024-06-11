<?class Logger
{
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
}?>