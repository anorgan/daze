<?php

namespace Daze;

use Daze\Command\Build;
use Daze\Command\CreateEntry;
use Daze\Command\Init;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    const DAZE_DIR      = '.daze';
    const CONFIG_FILE   = 'daze.yml';
    
    const ROUTE_HOME        = 'home';
    const ROUTE_ENTRY       = 'entry';
    const ROUTE_CATEGORY    = 'category';
    const ROUTE_TAG         = 'tag';
    
    protected $root;
    protected $config;
    
    /**
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Init();
        $commands[] = new Build();
        $commands[] = new CreateEntry();

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
                'themesPath'    => '.daze/themes',
                'theme'         => 'daze'
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

    public function getTypes()
    {
        return array(
            Entry::TYPE_MARKDOWN => 'Markdown', 
            Entry::TYPE_HTML => 'Pure, raw HTML'
        );
    }

    /**
     * 
     * @return \Daze\Entry[]
     */
    public function getEntries()
    {
        $directory  = new \RecursiveDirectoryIterator($this->getEntriesPath(), \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS);
        $iterator   = new \RecursiveIteratorIterator($directory);

        $entries    = array();
        foreach ($iterator as $info) {
            if (!array_key_exists(strtolower($info->getExtension()), $this->getTypes())) {
                continue;
            }
            $entry = \Daze\Entry::load($info->getPathname());
            $entry->setApplication($this);
            $entries[$entry->getSlug()] = $entry;
        }
        
        uasort($entries, function(\Daze\Entry $entryA, \Daze\Entry $entryB) {
            $dateA = $entryA->getDate()->getTimestamp();
            $dateB = $entryB->getDate()->getTimestamp();
            if ($dateA === $dateB) {
                return 0;
            }
            
            return $dateA > $dateB ? -1 : +1;
        });

        return $entries;
    }

    /**
     * 
     * @return UrlGenerator
     * @todo Extract to service
     */
    public function getRouter()
    {
        $baseUrl = isset($this->getConfig()['baseUrl']) ? rtrim($this->getConfig()['baseUrl'], '/') : '';
        
        $routesConfig = isset($this->getConfig()['routes']) ? $this->getConfig()['routes'] : array();
        
        if (!isset($routesConfig[self::ROUTE_HOME])) {
            $routesConfig[self::ROUTE_HOME] = '/';
        }
        
        if (!isset($routesConfig[self::ROUTE_ENTRY])) {
            $routesConfig[self::ROUTE_ENTRY] = '/{category_slug}/{slug}/';
        }
        
        if (!isset($routesConfig[self::ROUTE_CATEGORY])) {
            $routesConfig[self::ROUTE_CATEGORY] = '/{slug}/';
        }
        
        if (!isset($routesConfig[self::ROUTE_TAG])) {
            $routesConfig[self::ROUTE_TAG] = '/tag/{slug}/';
        }

        $routes = new RouteCollection();
        $routes->add(self::ROUTE_HOME,      new Route($baseUrl . $routesConfig[self::ROUTE_HOME]));
        $routes->add(self::ROUTE_ENTRY,     new Route($baseUrl . $routesConfig[self::ROUTE_ENTRY]));
        $routes->add(self::ROUTE_CATEGORY,  new Route($baseUrl . $routesConfig[self::ROUTE_CATEGORY]));
        $routes->add(self::ROUTE_TAG,       new Route($baseUrl . $routesConfig[self::ROUTE_TAG]));
        
        return new UrlGenerator($routes, new RequestContext);
    }

    /**
     * 
     * @return type
     * @todo Extract to service
     */
    public function getTemplate($name)
    {
        $template = new \Daze\Template($this->getConfig()['themesPath'], $this->getConfig()['theme'] .'/'. $name);
        return $template->get();
    }
}
