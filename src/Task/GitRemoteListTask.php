<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\OutputParser\RemoteListParser;

class GitRemoteListTask extends BaseTask implements CommandInterface
{
    protected string $action = 'remote';

    protected function getOptions(): array
    {
        return [
            '--verbose' => [
                'type' => 'flag',
                'value' => true,
            ],
        ] + parent::getOptions();
    }

    protected function runProcessOutputs(): static
    {
        $parser = new RemoteListParser();
        $items = $parser->parse($this->actionExitCode, $this->actionStdOutput, $this->actionStdError);

        $this->assets['git.remotes'] = $items;
        $this->assets['git.remotes.names'] = array_keys($items);
        $this->assets['git.remotes.fetch'] = [];
        $this->assets['git.remotes.push'] = [];
        foreach ($items as $name => $item) {
            $this->assets['git.remotes.fetch'][$name] = $item['fetch'] ?? '';
            $this->assets['git.remotes.push'][$name] = $item['push'] ?? '';
        }

        return $this;
    }
}
