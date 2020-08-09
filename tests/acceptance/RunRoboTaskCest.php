<?php

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Codeception\Example;
use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;
use Symfony\Component\Yaml\Yaml;

class RunRoboTaskCest extends CestBase
{
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

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertSame($expected, $actual, 'Robo task stdOutput');
        $i->assertRegExp(
            "/\\n \[Git branch list\] cd 'local' && git branch --format '(.+?)'\\n/",
            $stdError,
            'Robo task stdError'
        );
    }
    // endregion

    // region Task - GitCurrentBranchTask
    protected function currentBranchSuccessCases(): array
    {
        return [
            '1.0.x' => ['branchName' => '1.0.x'],
            '1.1.x' => ['branchName' => '1.1.x'],
            'personal/a' => ['branchName' => 'personal/a'],
            'personal/b/c' => ['branchName' => 'personal/b/c'],
        ];
    }

    /**
     * @dataProvider currentBranchSuccessCases
     */
    public function currentBranchSuccess(AcceptanceTester $i, Example $example): void
    {
        $roboTaskName = 'current-branch:success';
        $id = "$roboTaskName:{$example['branchName']}";
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName, $example['branchName']);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $i->assertSame(0, $exitCode, 'Robo task - exit code');
        $i->assertStringContainsString(
            "\n [Git current branch] git symbolic-ref 'HEAD'\n",
            $stdError,
            'Robo task - stdError'
        );
        $assets = Yaml::parse($stdOutput);
        $i->assertSame($example['branchName'], $assets['short'], 'Robo task - assets.short');
        $i->assertSame("refs/heads/{$example['branchName']}", $assets['long'], 'Robo task - assets.long');
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

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertStringContainsString('a.php', $stdOutput, 'Robo task stdOutput a.php');
        $i->assertStringContainsString('b.php', $stdOutput, 'Robo task stdOutput b.php');
    }
    // endregion

    // region Task - GitListStagedFilesTask
    public function listStagedFiles(AcceptanceTester $i)
    {
        $roboTaskName = 'list-staged-files';
        $id = $roboTaskName;
        $i->runRoboTask($id, GitRoboFile::class, $roboTaskName);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertStringContainsString('A - a.php', $stdOutput, 'Robo task stdOutput a.php');
        $i->assertStringContainsString('A - b.php', $stdOutput, 'Robo task stdOutput b.php');
    }
    // endregion

    // region Task - GitNumOfCommitsBetweenTask
    protected function numOfCommitsBetweenBasicCases(): array
    {
        return [
            '1.0.0..1.0.3' => [
                'expected' => 2,
                'refFrom' => '1.0.0',
                'refTo' => '1.0.3',
            ],
            '1.0.3..1.0.3' => [
                'expected' => 0,
                'refFrom' => '1.0.3',
                'refTo' => '1.0.3',
            ],
        ];
    }

    /**
     * @dataProvider numOfCommitsBetweenBasicCases
     */
    public function numOfCommitsBetweenBasic(AcceptanceTester $i, Example $example): void
    {
        $refRange = "{$example['refFrom']}..{$example['refTo']}";
        $roboTaskName = 'num-of-commits-between:basic';
        $id = "$roboTaskName:{$refRange}";
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName,
            $example['refFrom'],
            $example['refTo']
        );
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $i->assertSame("{$example['expected']}\n", $stdOutput, 'Robo task stdOutput');
        $i->assertStringContainsString(
            "git rev-list --count '{$refRange}",
            $stdError,
            'Robo task stdError'
        );
        $i->assertSame(0, $exitCode, 'Robo task exit code');
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

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertSame(
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

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertSame(
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

        $i->assertSame(0, $exitCode, 'Robo task exit code');
        $i->assertSame($expected, $actual, 'Robo task stdOutput');
    }
    // endregion
}
