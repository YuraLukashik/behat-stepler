<?php

namespace Fesor\Stepler\Controller;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\SuiteRegistry;
use Fesor\Stepler\Runner\StepRunner;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SteplerController implements Controller
{

    /**
     * @var StepRunner
     */
    private $stepRunner;

    /**
     * @var SuiteRegistry
     */
    private $suiteRepository;

    /**
     * @var string|null
     */
    private $suite;

    /**
     * @param StepRunner $stepRunner
     * @param SuiteRegistry $registry
     * @param string|null $suite
     */
    public function __construct(StepRunner $stepRunner, SuiteRegistry $registry, $suite)
    {
        $this->stepRunner = $stepRunner;
        $this->suiteRepository = $registry;
        $this->suite = $suite;
    }


    /**
     * @inheritdoc
     */
    public function configure(SymfonyCommand $command)
    {
        $command
            ->addOption(
                '--run-step', null, InputOption::VALUE_REQUIRED,
                'Run single step'
            )
            ->addOption(
                '--return-step-results', null, InputOption::VALUE_NONE,
                'Returns results of step execution'
            );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $step = $input->getOption('run-step')) {
            return null;
        }
        
        // try to find correct suite
        $result = $this->stepRunner->run($step, $this->findSuite());
        if (!$result->isPassed()) {
            $output->writeln('<error>Step not passed</error>');
            
            return 1;
        }

        if ($input->getOption('return-step-results')) {
            $output->writeln(json_encode($result->getCallResult()->getReturn()));
        } else {
            $output->writeln('<info>Step passed</info>');
        }

        return 0;
    }
    
    private function findSuite123()
    {
        $suites = $this->suiteRepository->getSuites();
        $a = 1;
        
        if (count($suites) > 0 && null === $this->suite) {
            return $suites[0];
        }
        
        foreach ($suites as $suite) {
            if ($this->suite === $suite->getName()) {
                return $suite;
            }
        }
        
        throw new \RuntimeException(sprintf('Suite %s not found', $this->suite));
    }


}
