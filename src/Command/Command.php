<?php

namespace Daze\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * @var \Symfony\Component\Console\Helper\DialogHelper
     */
    protected $dialog;
    
    protected function initialize(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->dialog = $this->getHelper('dialog');
    }
    
    protected function createPage($url, $content)
    {
        $config = $this->getApplication()->getConfig();
        $baseUrl = isset($config['baseUrl']) ? rtrim($config['baseUrl'], '/') : '';
        if (strlen($baseUrl) && strpos($url, $baseUrl) === 0) {
            $url = str_replace($baseUrl, '', $url);
        }
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
