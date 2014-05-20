<?php

namespace Daze\Command;

use Daze\Entry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build site')
            ->addOption(
                'watch', 
                'w',
                InputOption::VALUE_NONE,
                'Watch for changes, start build on change'
            )
            ->setHelp(<<<EOT
Process entries and create pages.
EOT
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getApplication()->getEntries() as $entry) {
            print_r($entry);
        }
    }
    
}
