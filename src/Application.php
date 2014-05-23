<?php

namespace Daze;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    const DAZE_DIR      = '.daze';
    const CONFIG_FILE   = 'daze.yml';
    
    const ROUTE_ENTRY   = 'entry';
    const ROUTE_CATEGORY   = 'category';
    const ROUTE_TAG   = 'tag';
    
    protected $root;
    protected $config;
    
    /**
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\Init();
        $commands[] = new Command\Build();

        return $commands;
    }

    public function getRoot()
    {
        if (null === $this->root) {
            $this->root = getcwd();
        }
        
        return $this->root;
    }

    public function setRoot($root)
    {
        $this->root = $root;
        
        return $this;
    }
    
    public function getDazeRoot()
    {
        return $this->getRoot() .'/'. self::DAZE_DIR;
    }

    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = array(
                'entriesPath'   => '.daze/entries',
                'templatesPath' => '.daze/templates',
                'template'      => 'daze'
            );
            $configFile = $this->getRoot() .'/'. self::CONFIG_FILE;
            if (file_exists($configFile) && is_readable($configFile)) {
                $this->config = Yaml::parse($configFile) + $this->config;
            }
        }
        
        return $this->config;
    }
    
    public function setConfig(array $config, $write = true)
    {
        $this->config = $config;
        
        if ($write) {
            $yaml = Yaml::dump($config);
            file_put_contents($this->getRoot() .'/'. self::CONFIG_FILE, $yaml);
        }
    }
    
    public function urlize($string)
    {
        
        $urlized = strtolower(trim(preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $string)), '-'));
        $urlized = preg_replace("/[\/_|+ -]+/", '-', $urlized);
        return trim($urlized, '-');
    }
    
    public function getEntriesPath()
    {
        return $this->getRoot() .'/'. $this->getConfig()['entriesPath'];
    }

    /**
     * 
     * @return Entry[]
     */
    public function getEntries()
    {
        $directory  = new \RecursiveDirectoryIterator($this->getEntriesPath(), \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS);
        $iterator   = new \RecursiveIteratorIterator($directory);

        $entries    = array();
        foreach ($iterator as $info) {
            $entry = Entry::load($info->getPathname());
            $entry->setApplication($this);
            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * 
     * @return \Symfony\Component\Routing\Generator\UrlGenerator
     * @todo Extract to service
     */
    public function getRouter()
    {
        $routes = new \Symfony\Component\Routing\RouteCollection();
        $routes->add(self::ROUTE_ENTRY, new \Symfony\Component\Routing\Route('/{category_slug}/{slug}/'));
        $routes->add(self::ROUTE_CATEGORY, new \Symfony\Component\Routing\Route('/{slug}/'));
        $routes->add(self::ROUTE_TAG, new \Symfony\Component\Routing\Route('/tag/{slug}/'));
        
        return new \Symfony\Component\Routing\Generator\UrlGenerator($routes, new \Symfony\Component\Routing\RequestContext);
    }

    /**
     * 
     * @return type
     * @todo Extract to service
     */
    public function getTemplate($name)
    {
        $templateName = $this->getConfig()['template'];
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->getConfig()['templatesPath']), array('autoescape' => 'html'));
        
        $twig->addFilter(new \Twig_SimpleFilter('render', function (Entry $entry) {
            switch ($entry->getType()) {
                case Entry::TYPE_MARKDOWN:
                    $parsedown  = new \Parsedown();
                    $html       = $parsedown->text($entry->getContent());

                    break;
                
                default:
                    throw new \Exception('Error while rendering entry, unknown type: '. $entry->getType());
            }

            return $html;
        }, array('is_safe' => array('html'))));
        
        return $twig->loadTemplate($templateName .'/'. $name .'.twig');
    }
}
