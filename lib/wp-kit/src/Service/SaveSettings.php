<?php

namespace GhostLabs\WPKit\Service;

use Exception;
use Generator;

class SaveSettings
{
    
    public function __construct(private readonly array $handlerMap)
    {
    }

    private array $payload = [];

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    public function run(): Generator
    {
        foreach ($this->payload as $key => $data) {
            try {
                $class = $this->handlerMap[$key] ?? null;

                if ($class === null) {
                    throw new Exception("No save handler registered for key '$key'.");
                }

                $instance = new $class();
                yield [$key => $instance->save($data)];
            } catch (Exception $exception) {
                yield [
                    'error' => [
                        $key => $exception->getMessage(),
                    ],
                ];
            }
        }
    }
}
