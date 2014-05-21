<?php

namespace Daze;

use Symfony\Component\Yaml\Yaml;

class Entry extends \ArrayObject
{
    const TYPE_MARKDOWN = 'md';

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
    
    public function getSlug()
    {
        $urlized = strtolower(trim(preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $this->getTitle())), '-'));
        $urlized = preg_replace("/[\/_|+ -]+/", '-', $urlized);
        return trim($urlized, '-');
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
    
    public function isDraft()
    {
        return isset($this->draft) && $this->draft === true;
    }

    public function getType()
    {
        if (isset($this->type)) {
            return $this->type;
        }
        
        if (null === $this->getFile()) {
            throw new \Exception('Error while getting type, type not set, and can not guess from file, which is null');
        }
        
        $extension = strtolower(pathinfo($this->getFile(), PATHINFO_EXTENSION));
        switch ($extension) {
            case 'md':
            case 'markdown':
                return 'md';

                break;
            
            default:
                return $extension;
                break;
        }
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
    
    public function toArray()
    {
        $array = $this->getArrayCopy();
        $array['slug'] = $this->getSlug();
        return $array;
    }

    /**
     * 
     * @param string $file
     * 
     * @return \Daze\Entry
     */
    public static function load($file)
    {
        $content = file_get_contents($file);
        if (0 === ($metaStart = strpos($content, '---'))) {
            // Get meta
            $metaEnd = strpos($content, '---', $metaStart +1);
            $meta    = substr($content, $metaStart, $metaEnd);
            $meta    = Yaml::parse($meta);
            $content = substr($content, $metaEnd + 3);
        } else {
            $meta    = array('title' => pathinfo($file, PATHINFO_FILENAME));
        }

        $entry = new Entry($meta);
        $entry->setContent($content);
        $entry->setFile($file);
        
        return $entry;
    }
}
