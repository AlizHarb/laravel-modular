<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Support;

use Illuminate\Support\Facades\File;

final class ModuleManifestValidator
{
    /**
     * Validate a module manifest file.
     *
     * @return array<int, string>
     */
    public function validate(string $modulePath, ?string $expectedDirectoryName = null): array
    {
        $manifestPath = rtrim($modulePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'module.json';

        if (! File::exists($manifestPath)) {
            return ['module.json is missing.'];
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode((string) File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return ["module.json contains invalid JSON: {$exception->getMessage()}"];
        }

        if (! is_array($decoded)) {
            return ['module.json must contain a JSON object.'];
        }

        /** @var array<string, mixed> $manifest */
        $manifest = $decoded;
        $errors = [];

        $this->requireString($manifest, 'name', $errors);
        $this->validateString($manifest, 'description', $errors);
        $this->validateString($manifest, 'version', $errors);
        $this->validateString($manifest, 'namespace', $errors);
        $this->validateString($manifest, 'provider', $errors, nullable: true);
        $this->validateString($manifest, 'route_prefix', $errors);
        $this->validateBoolean($manifest, 'removable', $errors);
        $this->validateBoolean($manifest, 'disableable', $errors);
        $this->validateStringList($manifest, 'requires', $errors);
        $this->validateStringList($manifest, 'conflicts', $errors);
        $this->validateStringList($manifest, 'provides', $errors);

        if (isset($manifest['providers'])) {
            $this->validateStringList($manifest, 'providers', $errors);
        }

        if (isset($manifest['authors']) && ! is_array($manifest['authors'])) {
            $errors[] = 'authors must be an array.';
        }

        if (isset($manifest['middleware']) && ! is_array($manifest['middleware'])) {
            $errors[] = 'middleware must be an object or array.';
        }

        if (isset($manifest['events']) && ! is_array($manifest['events'])) {
            $errors[] = 'events must be an object.';
        }

        if (isset($manifest['policies']) && ! is_array($manifest['policies'])) {
            $errors[] = 'policies must be an object.';
        }

        if (isset($manifest['version']) && is_string($manifest['version']) && ! preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $manifest['version'])) {
            $errors[] = 'version must be a semantic version like 1.2.0.';
        }

        if (isset($manifest['name'], $expectedDirectoryName) && is_string($manifest['name']) && $manifest['name'] !== $expectedDirectoryName) {
            $errors[] = "name [{$manifest['name']}] does not match directory [{$expectedDirectoryName}].";
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<int, string> $errors
     */
    private function requireString(array $manifest, string $key, array &$errors): void
    {
        if (! isset($manifest[$key]) || ! is_string($manifest[$key]) || trim($manifest[$key]) === '') {
            $errors[] = "{$key} is required and must be a non-empty string.";
        }
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<int, string> $errors
     */
    private function validateString(array $manifest, string $key, array &$errors, bool $nullable = false): void
    {
        if (! array_key_exists($key, $manifest) || ($nullable && $manifest[$key] === null)) {
            return;
        }

        if (! is_string($manifest[$key])) {
            $errors[] = "{$key} must be a string.";
        }
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<int, string> $errors
     */
    private function validateBoolean(array $manifest, string $key, array &$errors): void
    {
        if (array_key_exists($key, $manifest) && ! is_bool($manifest[$key])) {
            $errors[] = "{$key} must be a boolean.";
        }
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<int, string> $errors
     */
    private function validateStringList(array $manifest, string $key, array &$errors): void
    {
        if (! array_key_exists($key, $manifest)) {
            return;
        }

        if (! is_array($manifest[$key])) {
            $errors[] = "{$key} must be an array of strings.";

            return;
        }

        foreach ($manifest[$key] as $index => $value) {
            if (! is_string($value) || trim($value) === '') {
                $errors[] = "{$key}.{$index} must be a non-empty string.";
            }
        }
    }
}
