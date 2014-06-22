<?php

namespace Daze;

use Daze\Entry;

class Template
{
    protected $path;
    protected $name;
    protected $twig;
    
    public function __construct($path = null, $name = null)
    {
        $this->path = $path;
        $this->name = $name;
    }
    
    public function getPath()
    {
        return $this->path;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        if (null === $this->twig) {
            $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->getPath()), array('autoescape' => 'html'));

            $this->twig->addFilter(new \Twig_SimpleFilter('render', function (Entry $entry) {
                switch ($entry->getType()) {
                    case Entry::TYPE_MARKDOWN:
                        $parsedown  = new \Parsedown();
                        $html       = $parsedown->text($entry->getContent());

                        break;

                    case Entry::TYPE_HTML:
                        $html       = $entry->getContent();

                        break;

                    default:
                        throw new \Exception('Error while rendering entry, unknown type: '. $entry->getType());
                }

                return $html;
            }, array('is_safe' => array('html'))));
        
            $this->twig->addFilter(new \Twig_SimpleFilter('urlize', '\Daze\Application::urlize', array('is_safe' => array('html'))));
            $this->twig->addFunction(new \Twig_SimpleFunction('url', function($name, $parameters = array()) {
                $app = new Application;
                return $app->getRouter()->generate($name, $parameters);
            }));

            $this->twig->addGlobal('app', new Application);
        }

        return $this->twig;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
        return $this;
    }
    
    public function get()
    {
        return $this->getTwig()->loadTemplate($this->getName() .'.twig');
    }
}
