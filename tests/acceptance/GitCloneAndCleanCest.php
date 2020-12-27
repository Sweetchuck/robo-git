<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Sweetchuck\Robo\Git\Test\AcceptanceTester;
use Sweetchuck\Robo\Git\Test\Helper\RoboFiles\GitRoboFile;
use Symfony\Component\Yaml\Yaml;

class GitCloneAndCleanCest extends CestBase
{

    public function gitCloneAndCleanSuccess(AcceptanceTester $i)
    {
        $roboTaskName = 'clone-and-clean:success';
        $id = $roboTaskName;
        $i->runRoboTask(
            $id,
            GitRoboFile::class,
            'clone-and-clean:success'
        );

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $expected = [
            'exitCode' => 0,
            'stdOutput' => Yaml::dump(
                [
                    'wc' => [
                        'branches' => [
                            'refs/heads/live/main' => [
                                'isCurrentBranch' => false,
                                'push.short' => '',
                                'push.short.short' => '',
                                'refName' => 'refs/heads/live/main',
                                'refName.short' => 'live/main',
                                'upstream.short' => '',
                                'upstream.short.short' => '',
                            ],
                            'refs/heads/main' => [
                                'isCurrentBranch' =>  true,
                                'push.short' =>  '',
                                'push.short.short' =>  '',
                                'refName' =>  'refs/heads/main',
                                'refName.short' =>  'main',
                                'upstream.short' => '',
                                'upstream.short.short' =>  '',
                            ],
                        ],
                        'remotes' => [
                            'live' => '../release.git',
                        ],
                        'status' => [],
                    ],
                    'release' => [
                        'branches' => [
                            'refs/heads/main' => [
                                'isCurrentBranch' => true,
                                'push.short' => 'refs/remotes/release-store/main',
                                'push.short.short' => 'release-store/main',
                                'refName' => 'refs/heads/main',
                                'refName.short' => 'main',
                                'upstream.short' => 'refs/remotes/release-store/main',
                                'upstream.short.short' => 'release-store/main',
                            ],
                        ],
                        'remotes' => [
                            'release-store' => '../release.git',
                        ],
                        'status' => [
                            'a.txt' => ' D',
                        ],
                    ],
                ],
                99
            ),
            'stdError' => '[Git clone and clean]',
        ];

        $i->assertStringContainsString($expected['stdError'], $stdError, 'Robo task stdError');
        $i->assertStringContainsString($expected['stdOutput'], $stdOutput, 'Robo task stdOutput');
        $i->assertSame($expected['exitCode'], $exitCode, 'Robo task exit code');
    }
}
