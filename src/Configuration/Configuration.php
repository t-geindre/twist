<?php

namespace App\Configuration;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Parser;

class Configuration
{
    /** @var string */
    private $storagePath;

    /** @var Parser */
    private $parser;

    /** @var SerializerInterface */
    private $serializer;

    /** @var string */
    private $defaultConfigFile;

    /** @var array */
    private $config;

    /** @var bool */
    private $hasChanges = false;

    public function __construct(
        string $defaultConfigFile,
        Parser $parser,
        SerializerInterface $serializer,
        string $storagePath = null
    ) {
        $this->defaultConfigFile = $defaultConfigFile;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $this->storagePath = $storagePath ?? $this->getDefaultStoragePath();
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->config[$key] = $value;

        $this->hasChanges = true;
    }

    public function load()
    {
        $file = $this->storagePath;

        if (!file_exists($file)) {
            $file = $this->defaultConfigFile;
        }

        $this->config = $this->parser->parseFile($file);
    }

    public function persist()
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->storagePath, $this->serializer->serialize($this->config, 'yaml'));
    }

    protected function getDefaultStoragePath(): string
    {
        return ($_SERVER['HOME'] ?? ($_SERVER['HOMEDRIVE'].'/'.$_SERVER['HOMEPATH'])).'/.twitter/config.yaml';
    }
}
