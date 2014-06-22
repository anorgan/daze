<?php

namespace Daze\Command;

use Daze\Entry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntry extends Command
{
    protected function configure()
    {        
        $this
            ->setName('create:entry')
            ->setDescription('Create entry')
            ->setHelp(<<<EOT
Create new entry.
EOT
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config     = $this->getApplication()->getConfig();
        
        $entry = new Entry();
        $entry->setApplication($this->getApplication());

        $entry->setTitle($this->required($output, 'Title of entry: '));

        $possibleTypes = $this->getApplication->getTypes();

        $type = $this->select($output, 'Select type: ', $possibleTypes, self::DEFAULT_FIRST_CHOICE);

        if (isset($config['categories'])) {
            $categories = array_values($config['categories']);
            $categoryId = $this->select($output, 'Category: ', $categories, self::DEFAULT_FIRST_CHOICE);
            $entry->category = $categories[$categoryId];
        }

        $entry->setFile($this->getApplication()->getEntriesPath() .'/'. $entry->getSlug() .'.'. $type);
        
        switch ($type) {
            case Entry::TYPE_HTML:
                $entry->setContent('<h2>'. $entry->getTitle() .'</h2>'. PHP_EOL);
                
                break;
            case Entry::TYPE_MARKDOWN:
                $entry->setContent('# '. $entry->getTitle() . PHP_EOL);
                
                break;
        }

        $entry->save();
        
        $output->writeln('New entry has been created at <info>'. $entry->getFile() .'</info>');
    }
}
