<?php

namespace Twist\Configuration;

use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        string $defaultConfigFile,
        Parser $parser,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $storagePath = null
    ) {
        $this->defaultConfigFile = $defaultConfigFile;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $this->storagePath = $storagePath ?? $this->getDefaultStoragePath();
        $this->logger = $logger;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->config[$key] = $value;

        if (!file_exists($this->storagePath)) {
            $this->persist();
        }
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
        try {
            $dir = dirname($this->storagePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($this->storagePath, $this->serializer->serialize($this->config, 'yaml'));

            $this->logger->info(sprintf('Configuration saved in %s', $this->storagePath));
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Unable to save configuration in %s', $this->storagePath));
        }
    }

    protected function getDefaultStoragePath(): string
    {
        return ($_SERVER['HOME'] ?? ($_SERVER['HOMEDRIVE'].'/'.$_SERVER['HOMEPATH'])).'/.twitter/config.yaml';
    }
}
