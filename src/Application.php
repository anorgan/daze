<?php

namespace Daze;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    const CONFIG_FILE = 'daze.yml';
    
    protected $root;
    protected $config;
    
    /**
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\Init();

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
    
    public function getConfig()
    {
        if ($this->config === null) {
            $configFile = $this->getRoot() .'/'. self::CONFIG_FILE;
            if (file_exists($configFile) && is_readable($configFile)) {
                $this->config = Yaml::parse($configFile);
            } else {
                $this->config = array();
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
}
