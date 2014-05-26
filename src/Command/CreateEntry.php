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

        $entry->setTitle($this->dialog->ask($output, 'Title of entry: '));
        $possibleTypes = array(Entry::TYPE_HTML, Entry::TYPE_MARKDOWN);
        $type = $this->dialog->askAndValidate($output, 'Type <info>['. Entry::TYPE_MARKDOWN .']<info>: ', function($answer) use ($possibleTypes) {
            if (!in_array($answer, $possibleTypes)) {
                throw new \RuntimeException('Type not supported, possible types are: '. implode(', ', $possibleTypes));
            }
            return $answer;
        }, false, Entry::TYPE_MARKDOWN, $possibleTypes);
        
        $categories = $config['categories'];
        $entry->category = $this->dialog->askAndValidate($output, 'Category <info>['.reset($categories).']</info>: ', function($answer) use($categories) {
            if (!in_array($answer, $categories)) {
                throw new \RuntimeException('Pick one of the categories: '. implode(PHP_EOL, $categories));
            }
            return $answer;
        }, false, reset($categories), $categories);
        $entry->setFile($this->getApplication()->getDazeRoot() .'/entries/'. $entry->getSlug() .'.'. $type);
        $entry->setContent('# '. $entry->getTitle() . PHP_EOL);
        $entry->save();
        
        $output->writeln('New entry has been created at <info>'. $entry->getFile() .'</info>');
    }
}
