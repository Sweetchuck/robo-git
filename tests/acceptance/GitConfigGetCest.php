<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;

class GitConfigGetCest extends CestBase
{
    public function configGetBasic(AcceptanceTester $i)
    {
        $roboTaskName = 'config-get:basic';
        $id = $roboTaskName;
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName
        );

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $expected = [
            'exitCode' => 0,
            'stdOutput' => implode(PHP_EOL, [
                'user:',
                '    email: robo-git.test-runner@example.com',
                '',
            ]),
            'stdError' => "[Git - Config get] git config --local 'user.email'" . PHP_EOL,
        ];

        $i->assertContains($expected['stdError'], $stdError, 'Robo task stdError');
        $i->assertSame($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }

    public function configGetCopy(AcceptanceTester $i)
    {
        $roboTaskName = 'config-get:copy';
        $id = $roboTaskName;
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName
        );

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $expected = [
            'exitCode' => 0,
            'stdOutput' => implode(PHP_EOL, [
                'dstDir.git.config.user.name: Abc Def',
                'dstDir.git.config.user.email: abc.def@example.com',
                '',
            ]),
        ];

        $i->assertSame($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }
}
