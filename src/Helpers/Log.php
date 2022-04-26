<?php

namespace Moloni\Helpers;

class Log
{
    private static $fileName;
    private static $fileDir = _PS_MODULE_DIR_ . 'molonies/logs';

    /**
     * Logs a message in a log file.
     *      log file format: (company_id)_(date(ymd)).txt
     *      log folder location: modules/(this module name)/logs/
     *
     * @param string $message message that will be logged
     *
     * @return void
     */
    public static function writeLog($message)
    {
        if (!is_dir(self::$fileDir)) {
            mkdir(self::$fileDir);
        }

        if (empty(self::$fileName)) {
            self::$fileName = ((Moloni::get('company_id')) ? Moloni::get('company_id') . '_' : '0_')
                . date('Ymd')
                . '.txt';
        }

        $fp = fopen(self::$fileDir . '/' . self::$fileName, 'a');
        fwrite($fp, '[' . date('Y-m-d H:i:s') . '] : ' . $message . PHP_EOL);
        fclose($fp);
    }

    /**
     * Deletes all files inside the logs folder
     */
    public static function deleteLogs()
    {
        $logFiles = glob(self::$fileDir . '/*');

        foreach ($logFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Returns the file name
     *
     * @return mixed returns a string
     */
    public static function getFileName()
    {
        return self::$fileName;
    }

    /**
     * Sets the file name
     *
     * @param string $fileName name of the file
     *
     * @return bool returns true or false depending on the file name received
     */
    public static function setFileName($fileName)
    {
        if (!empty($fileName)) {
            self::$fileName = $fileName;

            return false;
        }

        return true;
    }
}
