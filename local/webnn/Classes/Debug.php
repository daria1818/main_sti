<?php
    namespace Classes;


    class Debug
    {
        public static $fp;
        public static $file;

        public static function write($text, $trace = '')
        {
            $date = date('Y-m-d');
            $link = __DIR__ . '/logs/'.$date.'.php';


            if (!file_exists($link)) {
                self::$fp = fopen($link, 'w');
                fwrite(self::$fp, '<?php /*' . PHP_EOL);
            } else {
                self::$fp = fopen($link, 'a+');
            }

            $textWrite = self::prLog($text);
            $backtrace = debug_backtrace(~DEBUG_BACKTRACE_PROVIDE_OBJECT, 0);
            self::$file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $backtrace[0]['file']);
            fwrite(self::$fp, '------ '.date('H:i:s').' ------------------------------- '.$backtrace[0]['line']. ' - '.self::$file . PHP_EOL);
            fwrite(self::$fp, $textWrite . PHP_EOL);
            if($trace) {
                $backtrace = self::prLog($backtrace);
                fwrite(self::$fp, $backtrace. PHP_EOL);
            }
        }

        public static function trace($limit = 0, $options = null, $skip = 1)
        {

            $trace = debug_backtrace(~DEBUG_BACKTRACE_PROVIDE_OBJECT, 0);
            return $trace;
        }


        public static function pr($data) {
            echo '<pre>' . print_r($data, 1) . '</pre>';
        }

        public static function prLog($data) {
            return print_r($data, 1);
        }

    }
