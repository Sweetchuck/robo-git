<?php

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Robo\Robo;
use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;
use Symfony\Component\Yaml\Yaml;

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

    public function _after()
    {
        Robo::createDefaultContainer();
    }

    public function currentBranchSuccess10x(AcceptanceTester $i): void
    {
        $this->currentBranchSuccess($i, '1.0.x');
    }

    public function currentBranchSuccess11x(AcceptanceTester $i): void
    {
        $this->currentBranchSuccess($i, '1.1.x');
    }

    public function currentBranchSuccessPersonalA(AcceptanceTester $i): void
    {
        $this->currentBranchSuccess($i, 'personal/a');
    }

    public function currentBranchSuccessPersonalBD(AcceptanceTester $i): void
    {
        $this->currentBranchSuccess($i, 'personal/b/d');
    }

    protected function currentBranchSuccess(AcceptanceTester $i, string $branchName): void
    {
        $roboTaskName = 'current-branch:success';
        $id = "$roboTaskName:$branchName";
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName, $branchName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $pattern = sprintf(
            "/%s '.+?' %s/",
            preg_quote('[Git current branch] cd'),
            preg_quote("&& git symbolic-ref 'HEAD'")
        );

        $i->assertEquals(0, $exitCode, 'Robo task - exit code');
        $i->assertRegExp($pattern, $stdError, 'Robo task - stdError');
        $assets = Yaml::parse($stdOutput);
        $i->assertEquals($branchName, $assets['short'], 'Robo task - assets.short');
        $i->assertEquals("refs/heads/$branchName", $assets['long'], 'Robo task - assets.long');
    }

    public function listFiles(AcceptanceTester $i): void
    {
        $roboTaskName = 'list:files';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertContains('a.php', $stdOutput, 'Robo task stdOutput a.php');
        $i->assertContains('b.php', $stdOutput, 'Robo task stdOutput b.php');
    }

    public function numOfCommitsBetweenBasicNormal(AcceptanceTester $i): void
    {
        $roboTaskName = 'num-of-commits-between:basic';
        $id = "$roboTaskName:normal";
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName,
            '1.0.0',
            '1.0.3'
        );
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $i->assertEquals("2\n", $stdOutput, 'Robo task stdOutput');
        $i->assertContains(
            "git rev-list --count '1.0.0..1.0.3'",
            $stdError,
            'Robo task stdError'
        );
        $i->assertEquals(0, $exitCode, 'Robo task exit code');
    }

    public function numOfCommitsBetweenBasicSame(AcceptanceTester $i): void
    {
        $roboTaskName = 'num-of-commits-between:basic';
        $id = "$roboTaskName:same";
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName,
            '1.0.3',
            '1.0.3'
        );
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $i->assertEquals("0\n", $stdOutput, 'Robo task stdOutput');
        $i->assertContains(
            "git rev-list --count '1.0.3..1.0.3'",
            $stdError,
            'Robo task stdError'
        );
        $i->assertEquals(0, $exitCode, 'Robo task exit code');
    }

    public function readStagedFilesWithContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-with-content';
        $id = $roboTaskName;

        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/contents.txt"),
            $stdOutput,
            'Robo task stdOutput'
        );
    }

    public function readStagedFilesWithoutContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read:staged-files-without-content';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/commands.txt"),
            $stdOutput,
            'Robo task stdOutput'
        );
    }

    public function tagListHuman(AcceptanceTester $i): void
    {
        $roboTaskName = 'tag:list-human';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/tag-list-human.txt"),
            $stdOutput,
            'Robo task stdOutput'
        );
        $i->assertContains('[Git tag list] cd', $stdError, 'Robo task stdError');
        $i->assertContains("&& git tag\n", $stdError, 'Robo task stdError');
    }

    public function tagListMachine(AcceptanceTester $i): void
    {
        $roboTaskName = 'tag:list-machine';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals(
            file_get_contents("{$this->expectedDir}/tag-list-machine.yml"),
            $stdOutput,
            'Robo task stdOutput'
        );
    }
}
