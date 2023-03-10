<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use League\Container\DefinitionContainerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Git\Test\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\Git\Test\UnitTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\ErrorHandler\BufferingLogger;

class TaskTestBase extends Unit
{
    protected UnitTester $tester;

    protected DefinitionContainerInterface $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo Git', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = (new RoboConfig());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this
            ->container
            ->addShared('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
    }
}
