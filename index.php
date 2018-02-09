<?php

// TODO: сделать поиск пересекающихся периодических запросов
// TODO: проверить верность счётчика количества запросов в интервале на своих данных.
// TODO: учитывать частоту запросов в секундах или миллисекундах? хммммм....

require_once('vendor/autoload.php');

define('INPUT_FILE_NAME', 'data.csv');
define('OUTPUT_FILE_NAME', 'result.txt');

$fileResource = fopen(INPUT_FILE_NAME, 'rb');
if (!$fileResource) {
    die("Can't create file's resource.");
}

/** @var \crabovwik\y\interfaces\TaskInterface[] $tasks */
$tasks = array(
    new \crabovwik\y\tasks\MaxRequestUsersTask(),
    new \crabovwik\y\tasks\MaxDataUsersTask(),
    new \crabovwik\y\tasks\PeriodSrcUserRequestsTask(),
//    new \crabovwik\y\tasks\PeriodSrcIpRequestsTask(),
//    new \crabovwik\y\tasks\NGrammTask(),
);

while (($line = fgets($fileResource)) !== false) {
    try {
        $lineRow = new \crabovwik\y\data\FileRow($line);
    } catch (RuntimeException $exception) {
        echo "\nSomething gone wrong in row creation: {$exception->getMessage()}\n";
        continue;
    }

    foreach ($tasks as $task) {
        $task->doLogic($lineRow);
    }
}

fclose($fileResource);

$resultFileResource = fopen(OUTPUT_FILE_NAME, 'wb');
foreach ($tasks as $task) {
    fwrite($resultFileResource, $task->getDescription() . "\n{$task->getResult()}\n");
}
