<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Tests\AcceptanceTester;
use Sweetchuck\Robo\Git\Tests\Helper\RoboFiles\GitRoboFile;

class GitRemoteListCest extends CestBase
{
    public function remoteListEmpty(AcceptanceTester $i): void
    {
        $roboTaskName = 'remote-list:empty';
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
                'git.remotes: {  }',
                'git.remotes.names: {  }',
                'git.remotes.fetch: {  }',
                'git.remotes.push: {  }',
                '',
                '',
            ]),
        ];

        $i->assertStringContainsString('git remote --verbose', $stdError, 'Robo task stdError');
        $i->assertSame($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }

    public function remoteListBasic(AcceptanceTester $i): void
    {
        $roboTaskName = 'remote-list:basic';
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
                'git.remotes:',
                '    origin:',
                '        fetch: ../remote',
                '        push: ../remote',
                'git.remotes.names:',
                '    - origin',
                'git.remotes.fetch:',
                '    origin: ../remote',
                'git.remotes.push:',
                '    origin: ../remote',
                '',
                '',
            ]),
        ];

        $i->assertStringContainsString('git remote --verbose', $stdError, 'Robo task stdError');
        $i->assertSame($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }
}
