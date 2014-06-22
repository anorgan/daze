<?php

namespace Daze\Command;

use Daze\Entry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Create project')
            ->setHelp(<<<EOT
Create directory structure, setup config file by asking a couple of questions
EOT
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createDirectoryStructure($output);
        
        $config = $this->getApplication()->getConfig();
        
        if (!isset($config['title'])) {
            $config['title'] = ucwords(str_replace(array('-', '_'), ' ', basename($this->getApplication()->getRoot())));
        }

        $config['title'] = $this->dialog->ask($output, 'Your new site title <info>['. $config['title'] .']<info>: ', $config['title']);

        $this->getApplication()->setConfig($config);
    }
    
    protected function createDirectoryStructure(OutputInterface $output)
    {
        $root = $this->getApplication()->getRoot();
        $structure = array(
            $this->getApplication()->getDazeRoot() .'/themes/daze',
            $this->getApplication()->getDazeRoot() .'/entries',
            'css',
            'js',
            'images',
        );
        
        foreach ($structure as $path) {
            $path = strpos($path, '/') === 0 ? $path : $root .'/'. $path;
            if (is_dir($path)) {
                continue;
            }
            mkdir($path, 0755, true);
            $output->writeln(sprintf('<info>Created directory %s</info>', $path));
        }
        
        // Add first entry
        $entry = new Entry();
        $entry
            ->setTitle('My First Entry')
            ->setFile($this->getApplication()->getEntriesPath() .'/my-first-entry.md')
            ->setContent('My First Entry
--------------

This is the first entry');
        
        $entry->save();
        
        // Add base template
        file_put_contents($this->getApplication()->getDazeRoot() .'/themes/daze/layout.twig', <<<EOT
<html>
<head>
</head>
<body>
    {{ entry|render }}
</body>
</html>
EOT
        );
    }
    
}
