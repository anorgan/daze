<?php

namespace Daze;

use Symfony\Component\Yaml\Yaml;

class Entry extends \ArrayObject
{
    protected $file;
    protected $content;
    
    public function __construct($array = array())
    {
        $this->setFlags(self::ARRAY_AS_PROPS);
        parent::__construct($array);
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function save()
    {
        $meta       = Yaml::dump($this->getArrayCopy());
        $content    = sprintf("---\n%s---\n%s", $meta, $this->getContent());
        file_put_contents($this->getFile(), $content);
    }
}
