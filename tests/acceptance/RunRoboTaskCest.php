<?php

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;

class RunRoboTaskCest
{
    /**
     * @var string
     */
    protected $expectedDir = 'tests/_data/expected';

    public function __construct()
    {
        $this->expectedDir = codecept_data_dir('expected');
    }

    public function readStagedFilesWithContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-with-content';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $i->assertEquals(0, $i->getRoboTaskExitCode($id));
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/contents.txt"),
            $i->getRoboTaskStdOutput($id)
        );
    }

    public function readStagedFilesWithoutContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-without-content';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $i->assertEquals(0, $i->getRoboTaskExitCode($id));
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/commands.txt"),
            $i->getRoboTaskStdOutput($id)
        );
    }

    public function listFiles(AcceptanceTester $i): void
    {
        $roboTaskName = 'list:files';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $i->assertEquals(0, $i->getRoboTaskExitCode($id));
        $i->assertContains('a.php', $i->getRoboTaskStdOutput($id));
        $i->assertContains('b.php', $i->getRoboTaskStdOutput($id));
    }

    public function tagListHuman(AcceptanceTester $i): void
    {
        $roboTaskName = 'tag:list-human';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $i->assertEquals(0, $i->getRoboTaskExitCode($id));
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/tag-list-human.txt"),
            $i->getRoboTaskStdOutput($id)
        );
        $i->assertEquals('', $i->getRoboTaskStdError($id));
    }

    public function tagListMachine(AcceptanceTester $i): void
    {
        $roboTaskName = 'tag:list-machine';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $i->assertEquals(0, $i->getRoboTaskExitCode($id));
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/tag-list-machine.yml"),
            $i->getRoboTaskStdOutput($id)
        );
        $i->assertEquals('', $i->getRoboTaskStdError($id));
    }
}
