<?php

namespace Daze;

use DateTime;
use Exception;
use Symfony\Component\Yaml\Yaml;

class Entry extends \ArrayObject
{
    const TYPE_MARKDOWN = 'md';

    
    protected $application;
    protected $file;
    protected $content;
    
    public function __construct($array = array())
    {
        $this->setFlags(self::ARRAY_AS_PROPS);
        parent::__construct($array);
    }
    
    /**
     * 
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * 
     * @param \Daze\Application $application
     * @return \Daze\Entry
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getSlug()
    {
        if (!isset($this->slug) || null === $this->slug) {
            $this->slug = $this->getApplication()->urlize($this->getTitle());
        }
        return $this->slug;
    }

    /**
     * 
     * @return DateTime
     * @throws Exception
     */
    public function getDate()
    {
        if (!isset($this->date)) {
            // No date set; get last modified time and save it
            $dateTime = DateTime::createFromFormat('U', filemtime($this->getFile()));
            $this->date = $dateTime->format('Y-m-j');
            $this->save();
        }

        if (is_numeric($this->date)) {
            $this->date = DateTime::createFromFormat('U', $this->date);
        } elseif (is_string($this->date)) {
            $this->date = DateTime::createFromFormat('Y-m-j', $this->date);
        } elseif (!($this->date instanceof DateTime)) {
            throw new Exception('Error getting date, can not create DateTime object from '. $this->date);
        }

        return $this->date;
    }
    
    public function getCategory()
    {
        if (!is_array($this->category)) {
            $flippedCategories = array_flip($this->getApplication()->getConfig()['categories']);
            $this->category = array(
                'slug' => $flippedCategories[$this->category],
                'title' => $this->category
            );
        }
        
        return $this->category;
    }
    
    public function getUrl()
    {
        $url = $this->getApplication()->getRouter()->generate(\Daze\Application::ROUTE_ENTRY, $this->toArray());
        return '/'. trim(parse_url($url, PHP_URL_PATH), '/') .'/';
    }

    public function getContent()
    {
        return $this->content;
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
        $array['date'] = $this->getDate();
        $array['category_slug'] = $this->getCategory()['slug'];
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

        $entry = new \Daze\Entry($meta);
        $entry->setContent($content);
        $entry->setFile($file);
        
        return $entry;
    }
}
