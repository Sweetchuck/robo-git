<?php

declare(strict_types = 1);


namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;

class GitListChangedFilesCest extends CestBase
{

    public function listChangedFiles(AcceptanceTester $i)
    {
        $roboTaskName = 'list-changed-files';
        $id = $roboTaskName;
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName,
            '1.0.0',
            '1.0.1'
        );

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $expected = [
            'exitCode' => 0,
            'stdOutput' => implode(PHP_EOL, [
                'M - a.php',
                'A - b.php',
                '',
            ]),
            'stdError' => "git --no-pager diff --no-color --name-status -z '1.0.0..1.0.1'" . PHP_EOL,
        ];

        $i->assertStringContainsString($expected['stdError'], $stdError, 'Robo task stdError');
        $i->assertSame($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }
}
