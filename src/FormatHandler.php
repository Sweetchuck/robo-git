<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

class FormatHandler implements FormatHandlerInterface
{
    /**
     * @var callable
     */
    protected $uniqueHash;

    public function __construct(?callable $uniqueHash = null)
    {
        $this->uniqueHash = $uniqueHash ?: [Utils::class, 'getUniqueHash'];
    }

    public function createMachineReadableFormatDefinition(array $properties): array
    {
        $properties += [
            'refName' => 'refname:strip=0'
        ];

        $definition = [
            'key' => 'refName',
            'refSeparator' => ($this->uniqueHash)(),
            'propertySeparator' => ($this->uniqueHash)(),
            'keyValueSeparator' => ' ',
            'properties' => $properties,
            'format' => [],
        ];

        foreach ($properties as $key => $pattern) {
            $definition['format'][$key] = "{$key}{$definition['keyValueSeparator']}%($pattern)";
        }

        $definition['format'] = implode($definition['propertySeparator'], $definition['format']);
        $definition['format'] .= $definition['refSeparator'];

        return $definition;
    }

    public function parseStdOutput(string $stdOutput, array $definition): array
    {
        $asset = [];
        $refs = explode("{$definition['refSeparator']}\n", $stdOutput);
        array_pop($refs);
        foreach ($refs as $refProperties) {
            $ref = [];
            $refProperties = explode($definition['propertySeparator'], $refProperties);
            foreach ($refProperties as $property) {
                list($key, $value) = explode($definition['keyValueSeparator'], $property, 2);
                $ref[$key] = $value;
            }

            $this->processRefProperties($ref, $definition);

            ksort($ref);

            $key = $ref[$definition['key']];
            $asset[$key] = $ref;
        }

        return $asset;
    }

    protected function processRefProperties(array &$ref, array $definition): static
    {
        foreach ($definition['properties'] as $propertyName => $fieldName) {
            switch ($fieldName) {
                case 'refname':
                case 'refname:strip=0':
                case 'push':
                case 'push:strip=0':
                case 'upstream':
                case 'upstream:strip=0':
                    $this->processRefPropertiesRefName($propertyName, $ref);
                    break;

                case 'HEAD':
                    $this->processRefPropertiesHead($propertyName, $ref);
                    break;

                case 'upstream:track':
                case 'upstream:track,nobracket':
                    $this->processRefPropertiesUpstreamTrack($propertyName, $ref);
                    break;
            }
        }

        return $this;
    }

    protected function processRefPropertiesRefName(string $key, array &$ref): static
    {
        $ref += [
            "$key.short" => preg_replace(
                '@^refs/(heads|tags|remotes)/@',
                '',
                $ref[$key]
            ),
        ];

        return $this;
    }

    protected function processRefPropertiesHead(string $key, array &$ref): static
    {
        $ref[$key] = (bool) trim($ref[$key]);

        return $this;
    }

    protected function processRefPropertiesUpstreamTrack(string $key, array &$ref): static
    {
        $value = trim($ref[$key], '[]');

        $additions = [
            "$key.gone" => $value === 'gone',
        ];

        // @todo Support for sync.
        foreach (['ahead', 'behind'] as $keySuffix) {
            $matches = [];
            preg_match("/$keySuffix (?P<numOfCommits>\d+)/", $value, $matches);
            $additions["$key.$keySuffix"] = $matches ? (int) $matches['numOfCommits'] : null;
        }

        $ref += $additions;

        return $this;
    }
}
