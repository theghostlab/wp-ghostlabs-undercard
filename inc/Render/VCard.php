<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;

final class VCard
{
    
    private const FOLD_AT = 75;

    private const CRLF = "\r\n";

    public function forAuthor(Author $author): string
    {
        $lines = ['BEGIN:VCARD', 'VERSION:4.0'];

        $lines[] = $this->line('FN', $author->name());
        $lines[] = $this->structuredName($author);

        $this->push($lines, 'TITLE', $author->jobTitle());
        $this->push($lines, 'ORG', $author->employer());
        $this->push($lines, 'EMAIL;TYPE=work', $author->publicEmail());
        $this->push($lines, 'TEL;TYPE=voice,work', $author->phone());
        $this->push($lines, 'NOTE', $author->bio());
        $this->push($lines, 'PHOTO', $author->avatarUrl(), raw: true);

        $address = $this->address($author);

        if ($address !== '') {
            $lines[] = 'ADR;TYPE=work:' . $address;
        }

        $this->push($lines, 'URL', $author->url(), raw: true);
        $this->push($lines, 'URL', $author->profileUrl(), raw: true);

        foreach ($author->socialProfiles() as $profile) {
            $lines[] = 'X-SOCIALPROFILE;TYPE=' . $this->escape($profile->platform()->value)
                . ':' . $profile->url();
        }

        $lines[] = 'END:VCARD';

        return implode(self::CRLF, array_map([$this, 'fold'], $lines)) . self::CRLF;
    }

    public function filenameFor(Author $author): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($author->name())) ?? '';
        $slug = trim($slug, '-');

        return ($slug !== '' ? $slug : 'contact') . '.vcf';
    }

    private function structuredName(Author $author): string
    {
        $display = $author->name();

        $last  = self::partIfAlreadyPublic($author->lastName(), $display);
        $first = self::partIfAlreadyPublic($author->firstName(), $display);

        if ($last === '' && $first === '') {
            $last = $display;
        }

        return 'N:' . $this->escape($last) . ';' . $this->escape($first) . ';;;';
    }

    private static function partIfAlreadyPublic(string $part, string $display): string
    {
        $part = trim($part);

        if ($part === '') {
            return '';
        }

        return mb_stripos($display, $part) === false ? '' : $part;
    }

    private function address(Author $author): string
    {
        $street   = $this->escape($author->street());
        $locality = $this->escape($author->locality());
        $country  = $this->escape($author->country());

        if ($street === '' && $locality === '' && $country === '') {
            return '';
        }

        return ';;' . $street . ';' . $locality . ';;;' . $country;
    }

    private function push(array &$lines, string $property, string $value, bool $raw = false): void
    {
        if (trim($value) === '') {
            return;
        }

        $lines[] = $raw
            ? $property . ':' . trim($value)
            : $this->line($property, $value);
    }

    private function line(string $property, string $value): string
    {
        return $property . ':' . $this->escape(trim($value));
    }

    private function escape(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", "\r", ',', ';'],
            ['\\\\', '\\n', '\\n', '\\n', '\\,', '\\;'],
            $value,
        );
    }

    private function fold(string $line): string
    {
        if (strlen($line) <= self::FOLD_AT) {
            return $line;
        }

        $folded    = mb_strcut($line, 0, self::FOLD_AT);
        $remainder = substr($line, strlen($folded));

        while ($remainder !== '') {
            $chunk      = mb_strcut($remainder, 0, self::FOLD_AT - 1);
            $folded    .= self::CRLF . ' ' . $chunk;
            $remainder  = substr($remainder, strlen($chunk));
        }

        return $folded;
    }
}
