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

    public function readStagedFilesWithContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-with-content';

        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i->runRoboTask($roboTaskName);
        $i->expectTheExitCodeToBe(0);
        $i->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/contents.txt"));
    }

    public function readStagedFilesWithoutContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-without-content';

        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i->runRoboTask($roboTaskName);
        $i->expectTheExitCodeToBe(0);
        $i->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/contents.txt"));
    }

    public function listFilesWithoutContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'list:files';

        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i->runRoboTask($roboTaskName);
        $i->expectTheExitCodeToBe(0);
        $i->seeThisTextInTheStdOutput('a.php');
        $i->seeThisTextInTheStdOutput('b.php');
    }
}
