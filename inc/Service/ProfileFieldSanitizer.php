<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\ProfileField;
use GHOSTLABS\UNDERCARD\Enum\FieldType;

final class ProfileFieldSanitizer
{
    
    public function sanitize(ProfileField $field, mixed $value): string|int|array
    {
        if ($field->isList()) {
            return $this->sanitizeList($field->type, $value);
        }

        if ($field->type === FieldType::Attachment) {
            return $this->attachment($value);
        }

        if ($field->type === FieldType::Boolean) {
            return $this->boolean($value);
        }

        if (! is_scalar($value)) {
            return '';
        }

        return $this->sanitizeScalar($field->type, (string) $value);
    }

    private function sanitizeScalar(FieldType $type, string $value): string
    {
        return match ($type) {
            FieldType::Url   => esc_url_raw(trim($value)),
            FieldType::Email => sanitize_email(trim($value)),
            FieldType::Year  => $this->year($value),
            default          => sanitize_text_field($value),
        };
    }

    private function attachment(mixed $value): int
    {
        if (! is_scalar($value)) {
            return 0;
        }

        $id = absint($value);

        return $id > 0 && wp_attachment_is_image($id) ? $id : 0;
    }

    private function boolean(mixed $value): string
    {
        if (is_string($value)) {
            $value = strtolower(trim($value));

            return in_array($value, ['1', 'true', 'on', 'yes'], true) ? '1' : '';
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value ? '1' : '';
        }

        return '';
    }

    private function sanitizeList(FieldType $type, mixed $value): array
    {
        if (is_string($value)) {

            $pattern = $type === FieldType::Terms ? '/[\r\n,]+/' : '/[\s,]+/';
            $split   = preg_split($pattern, $value, -1, PREG_SPLIT_NO_EMPTY);
            $value   = $split === false ? [] : $split;
        }

        if (! is_array($value)) {
            return [];
        }

        $scalarType = $type === FieldType::Terms ? FieldType::Text : FieldType::Url;
        $clean      = [];

        foreach ($value as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $item = $this->sanitizeScalar($scalarType, (string) $item);

            if ($item !== '') {
                $clean[] = $item;
            }
        }

        return $clean;
    }

    private function year(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d{4}$/', $value) === 1 ? $value : '';
    }
}
