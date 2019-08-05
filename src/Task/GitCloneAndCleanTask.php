<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\BuilderAwareInterface;
use Robo\Task\Vcs\loadTasks as VcsTaskLoader;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GitCloneAndCleanTask extends BaseTask implements BuilderAwareInterface
{
    use GitTaskLoader;
    use VcsTaskLoader;

    // region srcDir
    /**
     * @var string
     */
    protected $srcDir = '';

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
    /**
     * @var string
     */
    protected $remoteUrl = '';

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
    /**
     * @var string
     */
    protected $remoteBranch = 'production';

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
    /**
     * @var string
     */
    protected $localBranch = 'production';

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
    /**
     * @var string
     */
    protected $remoteName = 'live';

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

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    public function __construct(?Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        $srcDir = $this->getSrcDir();
        $remoteUrl = $this->getRemoteUrl();
        $dstDir = $this->getWorkingDirectory();

        $srcRemotes = $this->getRemotes($srcDir);
        $srcRemoteName = array_search($remoteUrl, $srcRemotes);

        $this->fs->mkdir($dstDir);

        if ($srcRemoteName !== false) {
            $this->runActionExisting();

            return $this;
        }

        $this->runActionClone();

        return $this;
    }

    protected function runActionExisting()
    {
        $tmpBranchName = md5((string) time());

        $srcDir = $this->getSrcDir();
        $srcGitDir = $this->fs->exists("$srcDir/.git") ? "$srcDir/.git" : $srcDir;

        $dstDir = $this->getWorkingDirectory();
        $dstGitDir = "$dstDir/.git";

        $this->fs->remove("$dstDir/.git");
        $this->fs->mirror($srcGitDir, $dstGitDir);

        $dstDir = $this->getWorkingDirectory();

        $task = $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($dstDir);

        foreach ($this->getRemotes($dstDir) as $name => $null) {
            $task->exec(sprintf('remote remove %s', escapeshellarg($name)));
        }

        $task->exec(sprintf(
            'remote add %s %s',
            $this->getRemoteName(),
            $this->getRemoteUrl()
        ));

        $task
            ->exec(sprintf(
                'fetch %s %s:%s',
                escapeshellarg($this->getRemoteName()),
                escapeshellarg($this->getRemoteBranch()),
                escapeshellarg($tmpBranchName)
            ))
            ->exec(sprintf(
                'git symbolic-ref HEAD %s',
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

        $this->deleteFiles($dstDir);

        return $this;
    }

    protected function runActionClone()
    {
        $dstDir = $this->getWorkingDirectory();

        $this
            ->taskGitStack($dstDir)
            ->exec(sprintf(
                'clone --origin=%s --branch=%s %s %s',
                escapeshellarg($this->getRemoteName()),
                escapeshellarg($this->getRemoteBranch()),
                escapeshellarg($this->getRemoteUrl()),
                escapeshellarg($dstDir)
            ))
            ->run()
            ->stopOnFail();

        $this->deleteFiles($dstDir);
    }

    protected function deleteFiles(string $dir)
    {
        $files = (new Finder())
            ->in($dir)
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->ignoreVCSIgnored(false)
            ->depth('== 0');

        $this->fs->remove($files);

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
