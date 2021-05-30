<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\BuilderAwareInterface;
use Robo\Task\Vcs\Tasks as VcsTaskLoader;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Symfony\Component\Filesystem\Filesystem;

class GitCloneAndCleanTask extends BaseTask implements BuilderAwareInterface
{
    use GitTaskLoader;
    use VcsTaskLoader;

    /**
     * @var string
     */
    protected $taskName = 'Git clone and clean';

    // region srcDir
    protected string $srcDir = '';

    public function getSrcDir(): string
    {
        return $this->srcDir;
    }

    /**
     * @return $this
     */
    public function setSrcDir(string $srcDir)
    {
        $this->srcDir = $srcDir;

        return $this;
    }
    // endregion

    // region remoteUrl
    protected string $remoteUrl = '';

    public function getRemoteUrl(): string
    {
        return $this->remoteUrl;
    }

    /**
     * @return $this
     */
    public function setRemoteUrl(string $remoteUrl)
    {
        $this->remoteUrl = $remoteUrl;

        return $this;
    }
    // endregion

    // region remoteBranch
    protected string $remoteBranch = 'production';

    public function getRemoteBranch(): string
    {
        return $this->remoteBranch;
    }

    /**
     * @return $this
     */
    public function setRemoteBranch(string $remoteBranch)
    {
        $this->remoteBranch = $remoteBranch;

        return $this;
    }
    // endregion

    // region localBranch
    protected string $localBranch = 'production';

    public function getLocalBranch(): string
    {
        return $this->localBranch;
    }

    /**
     * @return $this
     */
    public function setLocalBranch(string $localBranch)
    {
        $this->localBranch = $localBranch;

        return $this;
    }
    // endregion

    // region remoteName
    protected string $remoteName = 'live';

    public function getRemoteName(): string
    {
        return $this->remoteName;
    }

    /**
     * @return $this
     */
    public function setRemoteName(string $remoteName)
    {
        $this->remoteName = $remoteName;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (array_key_exists('srcDir', $options)) {
            $this->setSrcDir($options['srcDir']);
        }

        if (array_key_exists('remoteUrl', $options)) {
            $this->setRemoteUrl($options['remoteUrl']);
        }

        if (array_key_exists('remoteBranch', $options)) {
            $this->setRemoteBranch($options['remoteBranch']);
        }

        if (array_key_exists('localBranch', $options)) {
            $this->setLocalBranch($options['localBranch']);
        }

        if (array_key_exists('remoteName', $options)) {
            $this->setRemoteName($options['remoteName']);
        }
    }

    protected Filesystem $fs;

    public function __construct(?Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function runHeader()
    {
        $this->printTaskDebug(
            'src: "{src}" dst: "{dst}"',
            [
                'src' => $this->getSrcDir(),
                'dst' => $this->getWorkingDirectory(),
            ]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        $srcDir = $this->getSrcDir();
        $remoteUrl = $this->getRemoteUrl();

        $srcRemotes = $this->getRemotes($srcDir);
        $srcRemoteName = array_search($remoteUrl, $srcRemotes);

        return $srcRemoteName !== false ? $this->runActionExisting() : $this->runActionClone();
    }

    protected function runActionExisting()
    {
        $tmpBranchName = md5((string) time());

        $srcDir = $this->getSrcDir();
        $srcGitDir = $this->fs->exists("$srcDir/.git") ? "$srcDir/.git" : $srcDir;

        $dstDir = $this->getWorkingDirectory();

        $this->fs->mkdir($dstDir);
        $this->fs->remove("$dstDir/.git");
        $this->fs->mirror($srcGitDir, "$dstDir/.git");
        $this->fs->remove("$dstDir/.git/info/exclude");

        $task = $this
            ->taskGitStack($this->getGitExecutable())
            ->printOutput(false)
            ->printMetadata(false)
            ->dir($dstDir);

        foreach (array_keys($this->getRemotes($dstDir)) as $name) {
            $task->exec(sprintf('remote remove %s', escapeshellarg($name)));
        }

        $task
            ->exec(sprintf(
                'remote add %s %s',
                $this->getRemoteName(),
                $this->getRemoteUrl()
            ))
            ->exec(sprintf(
                'fetch %s %s:%s',
                escapeshellarg($this->getRemoteName()),
                escapeshellarg($this->getRemoteBranch()),
                escapeshellarg($tmpBranchName)
            ))
            ->exec(sprintf(
                'symbolic-ref HEAD %s',
                escapeshellarg("refs/heads/$tmpBranchName")
            ))
            ->exec('reset');

        foreach ($this->getBranchNames($dstDir) as $name) {
            if ($tmpBranchName === $name) {
                continue;
            }

            $task->exec(sprintf('branch -D %s', escapeshellarg($name)));
        }

        $task
            ->exec(sprintf(
                'branch -m %s',
                escapeshellarg($this->getLocalBranch())
            ))
            ->exec(sprintf(
                'branch --set-upstream-to=%s',
                escapeshellarg($this->getRemoteName() . '/' . $this->getRemoteBranch())
            ))
            ->run()
            ->stopOnFail();

        return $this;
    }

    protected function runActionClone()
    {
        $this
            ->taskGitStack()
            ->exec(sprintf(
                'clone --origin=%s --branch=%s --no-checkout --dissociate %s %s',
                escapeshellarg($this->getRemoteName()),
                escapeshellarg($this->getRemoteBranch()),
                escapeshellarg($this->getRemoteUrl()),
                escapeshellarg($this->getWorkingDirectory())
            ))
            ->run()
            ->stopOnFail();

        return $this;
    }

    protected function getRemotes(string $dir): array
    {
        $result = $this
            ->taskGitRemoteList()
            ->setWorkingDirectory($dir)
            ->run();

        return $result->wasSuccessful() ?
            $result['git.remotes.fetch']
            : [];
    }

    protected function getBranchNames(string $dir): array
    {
        $result = $this
            ->taskGitBranchList()
            ->setWorkingDirectory($dir)
            ->run()
            ->stopOnFail();

        $branches = [];
        foreach ($result['gitBranches'] as $branch) {
            $branches[] = $branch['refName.short'];
        }

        return $branches;
    }
}
