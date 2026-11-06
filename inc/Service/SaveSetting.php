<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Contracts\SaveSettingsHandlerInterface;

abstract class SaveSetting implements SaveSettingsHandlerInterface
{
    
    abstract protected function key(): string;

    abstract protected function sanitize(string $data): string;

    public function save(?string $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $value = $this->sanitize($data);

        (new OptionsService())->save($this->key(), $value);

        return [ 'success' => true, 'value' => $value ];
    }
}
