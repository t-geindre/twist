<?php

namespace Twist\Twitter\Task\Step\Action;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\FieldResolverTrait;
use Twist\Twitter\Task\FileDependantTrait;

class CsvExport implements ActionInterface, ConfigurableInterface
{
    use FieldResolverTrait;
    use FileDependantTrait;

    /** @var array */
    private $fields;

    /** @var resource */
    private $fileHandler;

    /** @var string */
    private $delimiter;

    /** @var string */
    private $enclosure;

    /** @var string */
    private $escapeChar;

    public function configure(array $config): void
    {
        $this->fileHandler = fopen($this->getFile($config, 'file', false, true), 'a');
        $this->fields = $this->getFields($config);

        $this->delimiter = (string) ($config['delimiter'] ?? ",");
        $this->enclosure = (string) ($config['enclosure'] ?? '"');
        $this->escapeChar = (string) ($config['escape_char'] ?? "\\");
    }

    public function execute(array $subject): ?array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $value = $this->resolveField($field, $subject);
            $data[] = is_string($value) ? $value : json_encode($value);
        }

        fputcsv($this->fileHandler, $data, $this->delimiter, $this->enclosure, $this->escapeChar);

        return $subject;
    }

    public function __destruct()
    {
        if (null !== $this->fileHandler) {
            fclose($this->fileHandler);
        }

        $this->fileHandler = null;
    }

    private function getFields(array $config): array
    {
        $fields = $config['fields'] ?? null;

        if (is_string($fields)) {
            return [$fields];
        }

        if (!is_array($fields)) {
            throw new \InvalidArgumentException('Fields config must be either a field list or a single field');
        }

        return $fields;
    }
}
