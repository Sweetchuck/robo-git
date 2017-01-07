<?php

namespace Cheppers\Robo\Git\Tests\Acceptance;

use AcceptanceTester;

class RunRoboTaskCest
{
    /**
     * @var string
     */
    protected $expectedDir = '_data/expected';

    public function __construct()
    {
        $this->expectedDir = codecept_data_dir('expected');
    }

    public function readStagedFilesWithContent(AcceptanceTester $i)
    {
        $roboTaskName = 'read:staged-files-with-content';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i
            ->runRoboTask($roboTaskName)
            ->expectTheExitCodeToBe(0)
            ->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/contents.txt"));
    }

    public function readStagedFilesWithoutContent(AcceptanceTester $i)
    {
        $roboTaskName = 'read:staged-files-without-content';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i
            ->runRoboTask($roboTaskName)
            ->expectTheExitCodeToBe(0)
            ->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/contents.txt"));
    }

    public function listFilesWithoutContent(AcceptanceTester $i)
    {
        $roboTaskName = 'list:files';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i
            ->runRoboTask($roboTaskName)
            ->expectTheExitCodeToBe(0)
            ->seeThisTextInTheStdOutput('a.php')
            ->seeThisTextInTheStdOutput('b.php');
    }
}
