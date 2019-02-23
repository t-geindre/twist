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

    /** @var bool */
    private $isLoaded = false;

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
        $this->load();

        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->config[$key] = $value;

        if (!file_exists($this->storagePath)) {
            $this->persist();
        }
    }

    public function load(): void
    {
        if ($this->isLoaded) {
            return;
        }

        $this->isLoaded = true;

        $file = $this->storagePath;

        if (!file_exists($file)) {
            if ($file != $this->getDefaultStoragePath()) {
                $this->logger->warning(sprintf('"%s" does not exist, default configuration loaded', $this->storagePath));
            }
            $file = $this->defaultConfigFile;
        }

        $this->config = $this->parser->parseFile($file);
        $this->config = $this->resolveParameters($this->config);

        if (!$this->get('database_path', false)) {
            $this->config['database_path'] = sprintf(
                '%s/%s.db',
                dirname($this->storagePath),
                pathinfo($this->storagePath, PATHINFO_FILENAME)
            );
        }
    }

    public function persist(): void
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

    public function getParameter($key)
    {
        $parameters = $this->get('parameters', []);

        if (!array_key_exists($key, $parameters)) {
            throw new \RuntimeException(sprintf('Parameter "%s" not found', $key));
        }

        return $parameters[$key];
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    protected function resolveParameters(array $config): array
    {
        foreach ($config as $key => $entry) {
            if ($key === 'parameters') {
                continue;
            }

            if (is_array($entry)) {
                $config[$key] = $this->resolveParameters($entry);
                continue;
            }

            if (preg_match('/%([a-z\._-]+)%/i', $entry, $matches)) {
                $config[$key] = $this->getParameter($matches[1]);
            }
        }

        return $config;
    }

    protected function getDefaultStoragePath(): string
    {
        return ($_SERVER['HOME'] ?? ($_SERVER['HOMEDRIVE'].'/'.$_SERVER['HOMEPATH'])).'/.twitter/config.yaml';
    }
}
