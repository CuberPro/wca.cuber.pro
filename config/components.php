<?php

return call_user_func(function ($dirNames){
    $components = [];
    foreach ($dirNames as $dirName) {
        $dir = @opendir($dirName);
        $tmp = [];
        if ($dir !== false) {
            while (($file = @readdir($dir)) !== false) {
                $filePath = realpath($dirName . DIRECTORY_SEPARATOR . $file);
                $fileName = basename($file);
                if (substr($fileName, -4) == '.php') {
                    $componentName = substr($fileName, 0, -4);
                    if (substr($componentName, -6) == '.local') {
                        $componentName = substr($componentName, 0, -6);
                        $tmp[$componentName] = require($filePath);
                        continue;
                    }
                    if (!isset($tmp[$componentName])) {
                        $tmp[$componentName] = require($filePath);
                    }
                }
            }
        }
        $components[] = $tmp;
    }
    $result = [];
    foreach ($components as $one) {
        $result = array_merge($result, $one);
    }
    return array_filter($result);
}, $COMPONENT_DIRS);
