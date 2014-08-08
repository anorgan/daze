<?php

namespace Daze\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    const DEFAULT_FIRST_CHOICE = 'DEFAULT_FIRST_CHOICE';

    /**
     * @var \Symfony\Component\Console\Helper\DialogHelper
     */
    protected $dialog;
    
    protected function initialize(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->dialog = $this->getHelper('dialog');
    }
    
    protected function select(\Symfony\Component\Console\Output\OutputInterface $output, $question, $choices, $default = null, $attempts = false, $errorMessage = 'Value "%s" is invalid', $multiselect = false)
    {
        $choiceKeys = array_keys($choices);
        $width = max(array_map('strlen', $choiceKeys));
        
        if (null !== $default && $default === self::DEFAULT_FIRST_CHOICE) {
            $default = reset($choiceKeys);
        }

        $messages = (array) $question;
        foreach ($choices as $key => $value) {
            if (null !== $default && $key === $default) {
                $isDefault = ' <comment>(default)</comment>';
            } else {
                $isDefault = '';
            }

            $messages[] = sprintf("  [<info>%-${width}s</info>] %s%s", $key, $value, $isDefault);
        }

        $output->writeln($messages);

        $result = $this->dialog->askAndValidate($output, '> ', function ($picked) use ($choices, $errorMessage, $multiselect) {
            // Collapse all spaces.
            $selectedChoices = str_replace(" ", "", $picked);

            if ($multiselect) {
                // Check for a separated comma values
                if (!preg_match('/^[a-zA-Z0-9_-]+(?:,[a-zA-Z0-9_-]+)*$/', $selectedChoices, $matches)) {
                    throw new \InvalidArgumentException(sprintf($errorMessage, $picked));
                }
                $selectedChoices = explode(",", $selectedChoices);
            } else {
                $selectedChoices = array($picked);
            }

            $multiselectChoices = array();

            foreach ($selectedChoices as $value) {
                if (empty($choices[$value])) {
                    throw new \InvalidArgumentException(sprintf($errorMessage, $value));
                }
                array_push($multiselectChoices, $value);
            }

            if ($multiselect) {
                return $multiselectChoices;
            }

            return $picked;
        }, $attempts, $default, $choiceKeys);

        return $result;
        
    }
    
    protected function required(\Symfony\Component\Console\Output\OutputInterface $output, $question, $errorMessage = 'Required')
    {
        return $this->dialog->askAndValidate($output, $question, function($answer) use ($errorMessage) {
            if (empty($answer)) {
                throw new \InvalidArgumentException($errorMessage);
            }
            return $answer;
        });
    }

    protected function createPage($url, $content)
    {
        $config     = $this->getApplication()->getConfig();
        $baseUrl    = isset($config['baseUrl']) ? rtrim($config['baseUrl'], '/') : '';
        $url        = trim($url, '/');
        if (strlen($baseUrl) && strpos($url, $baseUrl) === 0) {
            $url = str_replace($baseUrl, '', $url);
        }
        $path   = $this->getApplication()->getRoot() .'/'. parse_url($url, PHP_URL_PATH);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filename   = $path .'/index.html';

        if (!file_put_contents($filename, $content)) {
            throw new \Exception('Error while creating page on path '. $filename);
        }
    }
}
