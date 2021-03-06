<?php

namespace Daze\Command;

use Daze\Application;
use Daze\Command\Command;
use Daze\Entry;
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
        if ($input->getOption('watch')) {
            $this->watch($input, $output);
            return;
        }
        
        $config     = $this->getApplication()->getConfig();
        $entries    = $this->getApplication()->getEntries();
        $categories = array();
        $tags       = array();

        /* @var $entry Entry */
        foreach ($entries as $entry) {
            if ($entry->isDraft()) {
                continue;
            }

            if (isset($entry['category'])) {
                $categories[$entry['category']][] = $entry;
            }

            if (isset($entry['tags'])) {
                foreach ((array) $entry['tags'] as $tag) {
                    $tags[$tag][] = $entry;
                }
            }
            
            $output->writeln(sprintf('<info>Writing entry %s</info>', $entry->getTitle()));

            // Get entry url, render and store
            $content    = $this->getApplication()->getTemplate('entry')->render(compact('entry', 'config'));

            $this->createPage($entry->getUrl(), $content);
        }

        // Create homepage
        $content    = $this->getApplication()->getTemplate('home')->render(compact('entries', 'config'));
        $this->createPage('/', $content);
        
        // Create categories
        if (isset($config['categories'])) {
            $flippedCategories = array_flip($config['categories']);
            foreach ($categories as $title => $entries) {
                $slug = $flippedCategories[$title];

                $url = $this->getApplication()->getRouter()->generate(Application::ROUTE_CATEGORY, compact('slug'));

                $content = $this->getApplication()->getTemplate('category')->render(compact('title', 'entries', 'config'));
                $this->createPage($url, $content);
            }
        }
            

        // Create tags pages
        foreach ($tags as $title => $entries) {
            $slug = $this->getApplication()->urlize($title);

            $url = $this->getApplication()->getRouter()->generate(Application::ROUTE_TAG, compact('slug'));

            $content = $this->getApplication()->getTemplate('tag')->render(compact('title', 'entries', 'config'));
            $this->createPage($url, $content);
        }
    }
    
    protected function watch(InputInterface $input, OutputInterface $output)
    {
        if (strpos(exec('which inotifywait'), 'inotifywait') === false) {
            throw new \Exception('Unable to watch, inotifywait not found on the system, install inotify-tools');
        }

        $cmd = sprintf('inotifywait -q -r -e close_write %s',
            $this->getApplication()->getEntriesPath() .'/'
        );
        
        $output->writeln('<comment>Watching for changes in '. $this->getApplication()->getEntriesPath() .'</comment>');

        $input->setOption('watch', false);
        while (exec($cmd)) {
            $this->execute($input, $output);
        }
    }
}
