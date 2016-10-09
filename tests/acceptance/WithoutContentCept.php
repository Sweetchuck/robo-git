<?php

/**
 * @var \Codeception\Scenario $scenario
 */

$roboTaskName = 'without:content';
$expectedDir = codecept_data_dir('expected');

$i = new AcceptanceTester($scenario);
$i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");

$i
    ->runRoboTask($roboTaskName)
    ->expectTheExitCodeToBe(0)
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/contents.txt"));
