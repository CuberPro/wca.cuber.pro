<?php

namespace app\commands;

use Yii;
use yii\console\Exception;
use yii\db\Query;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use yii\console\controllers\MessageController;

/**
 * This command extends some functionalities of message.
 *
 * @author Yunqi Ouyang
 */
class MessageExController extends MessageController {

	public $defaultAction = 'extract-db';

    /**
     * Extracts messages to be translated from db.
     *
     * This command will scan the specified db and extract
     * messages that need to be translated in different languages.
     *
     * @param string $configFile the path or alias of the configuration file.
     * You may use the "yii message/config" command to generate
     * this file and then customize it for your needs.
     * @throws Exception on failure.
     */
    public function actionExtractDb($configFile)
    {
        $this->initConfig($configFile);

        $dbCategories = $this->config['messageSource'];

        $messages = [];
        foreach ($dbCategories as $category => $dbColumns) {
        	$messages = array_merge_recursive($messages, [$category => $this->extractDbMessages($dbColumns)]);
        }
        foreach ($this->config['languages'] as $language) {
            $dir = $this->config['messagePath'] . DIRECTORY_SEPARATOR . $language;
            if (!is_dir($dir)) {
                @mkdir($dir);
            }
            $this->saveMessagesToPHP($messages, $dir, $this->config['overwrite'], $this->config['removeUnused'], $this->config['sort'], $this->config['markUnused']);
        }
    }

    /**
     * @param string $configFile
     * @throws Exception If configuration file does not exists.
     */
    private function initConfig($configFile)
    {
        parent::initConfig($configFile);
        $this->config = array_merge([
            'overwrite' => false,
            'sort' => false,
        ], $this->config);
        if (!isset($this->config['messageSource'])) {
            throw new Exception('The configuration file must specify "messageSource".');
        }
    }

    private function extractDbMessages($dbColumns) {
    	$ret = array();
    	foreach ($dbColumns as $column) {
	    	$table = $column['table'];
	    	$columnName = $column['column'];
	    	if (isset($column['where'])) {
	    		$where = $column['where'];
	    	}
	    	$query = (new Query())
	    		->select($columnName)
	    		->from($table);
	    	if (isset($where)) {
	    		$query->where($where);
	    	}
	    	$result = $query->all();
	    	$result = array_column($result, $columnName);
	    	$ret = array_merge($ret, $result);
    	}
    	return $ret;
    }
}
