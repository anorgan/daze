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
        /* @var $entry Entry */
        foreach ($this->getApplication()->getEntries() as $entry) {
            if ($entry->isDraft()) {
                continue;
            }

            // Get entry url, render and store
            $url    = $this->getApplication()->getRouter()->generate(\Daze\Application::ROUTE_ENTRY, $entry->toArray());
            $path   = $this->getApplication()->getRoot() .'/'. trim(parse_url($url, PHP_URL_PATH), '/');
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $filename   = $path .'/index.html';

            $content    = $this->getApplication()->getTemplate()->render(compact('entry'));

            if (!file_put_contents($filename, $content)) {
                throw new Exception('Error while storing entry "'. $entry->getTitle() .'" to '. $filename);
            }
        }

        // Create homepage
        // Create tags pages
    }
}
