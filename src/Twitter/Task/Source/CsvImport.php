<?php

namespace Twist\Twitter\Task\Source;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\FieldResolverTrait;
use Twist\Twitter\Task\FileDependantTrait;

class CsvImport implements SourceInterface, ConfigurableInterface
{
    use FileDependantTrait;
    use FieldResolverTrait;

    /** @var string */
    private $file;

    /** @var null|array */
    private $headers;

    /** @var string */
    private $delimiter;

    /** @var string */
    private $enclosure;

    /** @var string */
    private $escapeChar;

    public function execute(): array
    {
        $data = [];
        $currentLine = 0;
        $fileHandler = fopen($this->file, 'r');

        if (null === $this->headers) {
            $currentLine++;
            $this->headers = fgetcsv($fileHandler, 0, $this->delimiter, $this->enclosure, $this->escapeChar);
        }

        while ($line = fgetcsv($fileHandler, 0, $this->delimiter, $this->enclosure, $this->escapeChar)) {
            $currentLine++;

            if (count($this->headers) !== count($line)) {
                throw new \InvalidArgumentException(
                    sprintf("Headers count doesn't match items count (line %d)", $currentLine)
                );
            }

            $finalLine = [];
            foreach (array_combine($this->headers, $line) as $field => $value) {
                $finalLine = $this->reverseField($field, $value, $finalLine);
            };

            $data[] = $finalLine;
        }

        fclose($fileHandler);

        return $data;
    }

    public function configure(array $config): void
    {
        $this->file = $this->getFile($config, 'file', true);
        $this->configureHeaders($config);

        $this->delimiter = (string) ($config['delimiter'] ?? ",");
        $this->enclosure = (string) ($config['enclosure'] ?? '"');
        $this->escapeChar = (string) ($config['escape_char'] ?? "\\");
    }

    private function configureHeaders(array $config): void
    {
        $this->headers = null;

        if (is_array($config['headers'] ?? null)) {
            if ((bool) ($config['use_file_headers'] ?? false)) {
                throw new \InvalidArgumentException('Specified headers will be overwritten by file headers');
            }

            $this->headers = $config['headers'];
        }
    }
}
