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
        $flippedCategories = array_flip($config['categories']);
        foreach ($categories as $title => $entries) {
            $slug = $flippedCategories[$title];

            $url = $this->getApplication()->getRouter()->generate(\Daze\Application::ROUTE_CATEGORY, compact('slug'));

            $content = $this->getApplication()->getTemplate('category')->render(compact('title', 'entries', 'config'));
            $this->createPage($url, $content);
        }

        // Create tags pages
        foreach ($tags as $title => $entries) {
            $slug = $this->getApplication()->urlize($title);

            $url = $this->getApplication()->getRouter()->generate(\Daze\Application::ROUTE_TAG, compact('slug'));

            $content = $this->getApplication()->getTemplate('tag')->render(compact('title', 'entries', 'config'));
            $this->createPage($url, $content);
        }
    }
    
    protected function createPage($url, $content)
    {
        $path   = $this->getApplication()->getRoot() .'/'. trim(parse_url($url, PHP_URL_PATH), '/');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filename   = $path .'/index.html';

        if (!file_put_contents($filename, $content)) {
            throw new Exception('Error while creating page on path '. $filename);
        }
    }
}
