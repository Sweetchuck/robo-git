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

    // region Task - GitBranchListTask
    public function branchListBasic(AcceptanceTester $i): void
    {
        $roboTaskName = 'branch-list:basic';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $expected = [
            'refs/heads/8.x-1.x' => [
                'isCurrentBranch' => false,
                'push' => 'refs/remotes/origin/8.x-1.x',
                'push.short' => 'origin/8.x-1.x',
                'refName' => 'refs/heads/8.x-1.x',
                'refName.short' => '8.x-1.x',
                'track' => '',
                'track.ahead' => null,
                'track.behind' => null,
                'track.gone' => false,
                'upstream' => 'refs/remotes/origin/8.x-1.x',
                'upstream.short' => 'origin/8.x-1.x',
            ],
            'refs/heads/master' => [
                'isCurrentBranch' => true,
                'push' => 'refs/remotes/origin/master',
                'push.short' => 'origin/master',
                'refName' => 'refs/heads/master',
                'refName.short' => 'master',
                'track' => '',
                'track.ahead' => null,
                'track.behind' => null,
                'track.gone' => false,
                'upstream' => '',
                'upstream.short' => '',
            ],
        ];
        $actual = Yaml::parse($stdOutput);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals($expected, $actual, 'Robo task stdOutput');
        $i->assertRegExp(
            "/\\n \[Git branch list\] cd 'local' && git branch --format '(.+?)'\\n/",
            $stdError,
            'Robo task stdError'
        );
    }
    // endregion

    // region Task - GitCurrentBranchTask
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

        $i->assertEquals(0, $exitCode, 'Robo task - exit code');
        $i->assertContains(
            "\n [Git current branch] git symbolic-ref 'HEAD'\n",
            $stdError,
            'Robo task - stdError'
        );
        $assets = Yaml::parse($stdOutput);
        $i->assertEquals($branchName, $assets['short'], 'Robo task - assets.short');
        $i->assertEquals("refs/heads/$branchName", $assets['long'], 'Robo task - assets.long');
    }
    // endregion

    // region Task - GitListFilesTask

    public function listFiles(AcceptanceTester $i): void
    {
        $roboTaskName = 'list-files';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertContains('a.php', $stdOutput, 'Robo task stdOutput a.php');
        $i->assertContains('b.php', $stdOutput, 'Robo task stdOutput b.php');
    }
    // endregion

    // region Task - GitNumOfCommitsBetweenTask
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
    // endregion

    // region Task - GitReadStagedFilesTask
    public function readStagedFilesWithContent(AcceptanceTester $i): void
    {
        $roboTaskName = 'read-staged-files:with-content';
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
        $roboTaskName = 'read-staged-files:without-content';
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
    // endregion

    // region Task - GitTagListTask
    public function tagListBasic(AcceptanceTester $i): void
    {
        $roboTaskName = 'tag-list:basic';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);
        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $expected = [
            'refs/tags/1.0.0' => [
                'objectName' => 'SHA-1',
                'objectType' => 'commit',
                'refName' => 'refs/tags/1.0.0',
                'refName.short' => '1.0.0',
                'taggerDate' => '',
            ],
            'refs/tags/1.0.1' => [
                'objectName' => 'SHA-2',
                'objectType' => 'commit',
                'refName' => 'refs/tags/1.0.1',
                'refName.short' => '1.0.1',
                'taggerDate' => '',
            ],
            'refs/tags/1.0.2' => [
                'objectName' => 'SHA-3',
                'objectType' => 'commit',
                'refName' => 'refs/tags/1.0.2',
                'refName.short' => '1.0.2',
                'taggerDate' => '',
            ],
        ];
        $actual = Yaml::parse($stdOutput);

        $i->assertEquals(0, $exitCode, 'Robo task exit code');
        $i->assertEquals($expected, $actual, 'Robo task stdOutput');
    }
    // endregion
}
