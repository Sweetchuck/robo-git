<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;

class GitConfigSetCest extends CestBase
{
    public function configSetBasic(AcceptanceTester $i)
    {
        $roboTaskName = 'config-set:basic';
        $id = $roboTaskName;
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            $roboTaskName,
        );

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);

        $expected = [
            'exitCode' => 0,
            'stdOutput' => "mygroup.myname: myValue\n",
        ];

        $i->assertStringContainsString($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }
}
