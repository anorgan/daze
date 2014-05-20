<?php

namespace Daze\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
        $this->createDirectoryStructure();
        
        $config = $this->getApplication()->getConfig();
        
        /* @var $dialog DialogHelper */
        $dialog = $this->getHelper('dialog');
        
        if (!isset($config['title'])) {
            $config['title'] = ucwords(str_replace(array('-', '_'), ' ', basename($this->getApplication()->getRoot())));
        }

        $config['title'] = $dialog->ask($output, 'Your new site title <info>['. $config['title'] .']<info>: ', $config['title']);

        $this->getApplication()->setConfig($config);
    }
    
    protected function createDirectoryStructure()
    {
        $root = $this->getApplication()->getRoot();
        $structure = array(
            '.daze/assets',
            '.daze/layouts',
            '.daze/entries',
            'css',
            'js',
            'images',
        );
        
        foreach ($structure as $path) {
            if (is_dir($root .'/'. $path)) {
                continue;
            }
            mkdir($root .'/'. $path, 0755, true);
        }
        
        $entry = new \Daze\Entry();
        $entry
            ->setTitle('My First Entry')
            ->setFile($root .'/.daze/entries/my-first-entry.md')
            ->setContent('My First Entry
--------------

This is the first entry');
        
        $entry->save();
    }
    
}
