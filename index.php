<?php

/*
 * TODO: теоретически, каждый запрос может участвовать в нескольких возможных переодических запросах?
 */

/*
 * TODO: создать на объекте обвязки метод, который получает в качестве параметра объект, который был добавлен
 * TODO: в какую-либо цепочку (было 2 объекка в ней, этот стал третий). Для того, чтобы мы удалили добавленный
 * TODO: объект из тех связей, в которых он был добавлен ранее. Видимо придётся завести хэш-мапу, в которой будут
 * TODO: храниться ключи общей карты для поиска этого объекта и быстрого удаления, чтобы избежать пробега по всей карте.
 */

/*
 * TODO: при добавлении нового запроса в обработку я не могу сказать точно относится ли он к какому-либо одному уже
 * TODO: добавленному, так как определить переодичность запроса можно минимум по существующим двум запросам (учитывая, что
 * TODO: этот - третий). Таким образом, нужно строить кучу связей, которые будут жрать память. Нужно придумать как этого
 * TODO: избежать.
 */

// TODO: не факт, что запросы в логе будут идти по порядку (вообще факт, но мало ли). Нужно сортировать или проверять время.
// TODO: сделать поиск пересекающихся периодических запросов
// TODO: проверить верность счётчика количества запросов в интервале на своих данных.
// TODO: учитывать частоту запросов в секундах или миллисекундах? хммммм....
set_time_limit(0);
ini_set("memory_limit", "-1");
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
