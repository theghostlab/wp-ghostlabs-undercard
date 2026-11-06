<?php

namespace GHOSTLABS\UNDERCARD\Entity;

use InvalidArgumentException;
use WP_User;

final class Author
{
    private const SCHEMA_ANCHOR = '#person';

    public const AVATAR_SIZE = 192;

    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $profileUrl,
        private readonly string $bio,
        private readonly string $url,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly bool $isGuest,
        private readonly string $jobTitle,
        private readonly string $employer,
        private readonly string $employerUrl,
        private readonly string $education,
        private readonly array $expertise,
        private readonly string $handle,
        private readonly int $userId,
        private readonly int $photoId,
        private readonly string $guestAvatarUrl,
        private readonly string $phone,
        private readonly string $publicEmail,
        private readonly string $street,
        private readonly string $locality,
        private readonly string $country,
        private readonly string $estYear,
        private readonly string $statusLabel,
        private readonly string $ctaLabel,
        private readonly string $ctaUrl,
        private readonly array $socialProfiles,
        private readonly string $registeredAt,
        private readonly bool $vcardOptOut,
    ) {
    }

    public static function fromWpUser(WP_User $user): self
    {
        $profileUrl = (string) get_author_posts_url($user->ID);

        self::assertProfileUrl($profileUrl);

        return new self(
            id: 'user:' . $user->ID,
            name: $user->display_name,
            profileUrl: $profileUrl,
            bio: $user->description,
            url: $user->user_url,
            firstName: $user->first_name,
            lastName: $user->last_name,
            isGuest: false,
            jobTitle: self::metaString($user->ID, 'undercard_job_title'),
            employer: self::metaString($user->ID, 'undercard_employer'),
            employerUrl: self::metaString($user->ID, 'undercard_employer_url'),
            education: self::metaString($user->ID, 'undercard_education'),
            expertise: self::toTermList(get_user_meta($user->ID, 'undercard_expertise', true)),
            handle: self::buildHandle(
                self::metaString($user->ID, 'undercard_handle'),
                $user->user_nicename
            ),
            userId: $user->ID,
            photoId: (int) self::metaString($user->ID, 'undercard_avatar_id'),
            guestAvatarUrl: '',
            phone: self::metaString($user->ID, 'undercard_phone'),

            publicEmail: self::metaString($user->ID, 'undercard_public_email'),
            street: self::metaString($user->ID, 'undercard_street'),
            locality: self::metaString($user->ID, 'undercard_locality'),
            country: self::metaString($user->ID, 'undercard_country'),
            estYear: self::toFoundingYear(self::metaString($user->ID, 'undercard_est_year')),
            statusLabel: self::metaString($user->ID, 'undercard_status_label'),
            ctaLabel: self::metaString($user->ID, 'undercard_cta_label'),
            ctaUrl: self::metaString($user->ID, 'undercard_cta_url'),
            socialProfiles: SocialProfile::fromUrls(
                self::toUrlList(get_user_meta($user->ID, 'undercard_social', true))
            ),
            registeredAt: $user->user_registered,
            vcardOptOut: self::metaString($user->ID, 'undercard_vcard_optout') === '1',
        );
    }

    public static function guest(
        string $slug,
        string $name,
        string $profileUrl,
        string $bio = '',
        string $url = '',
        string $firstName = '',
        string $lastName = '',
        string $jobTitle = '',
        string $employer = '',
        string $employerUrl = '',
        string $education = '',
        array $expertise = [],
        string $handle = '',
        string $avatarUrl = '',
        string $phone = '',
        string $publicEmail = '',
        string $street = '',
        string $locality = '',
        string $country = '',
        string $estYear = '',
        string $statusLabel = '',
        string $ctaLabel = '',
        string $ctaUrl = '',
        array $socialUrls = [],
        int $photoId = 0,
    ): self {
        $slug = trim($slug);

        if ($slug === '') {
            throw new InvalidArgumentException('A guest author requires a slug to build a stable id.');
        }

        self::assertProfileUrl($profileUrl);

        return new self(
            id: 'guest:' . $slug,
            name: $name,
            profileUrl: $profileUrl,
            bio: $bio,
            url: $url,
            firstName: $firstName,
            lastName: $lastName,
            isGuest: true,
            jobTitle: $jobTitle,
            employer: $employer,
            employerUrl: $employerUrl,
            education: trim($education),
            expertise: self::toTermList($expertise),
            handle: self::buildHandle($handle, ''),
            userId: 0,

            photoId: max(0, $photoId),
            guestAvatarUrl: trim($avatarUrl),
            phone: $phone,
            publicEmail: $publicEmail,
            street: $street,
            locality: $locality,
            country: $country,
            estYear: self::toFoundingYear($estYear),
            statusLabel: trim($statusLabel),
            ctaLabel: trim($ctaLabel),
            ctaUrl: trim($ctaUrl),
            socialProfiles: SocialProfile::fromUrls($socialUrls),
            registeredAt: '',

            vcardOptOut: false,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function registeredAt(): string
    {
        return $this->registeredAt;
    }

    public function isGuest(): bool
    {
        return $this->isGuest;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function bio(): string
    {
        return $this->bio;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function profileUrl(): string
    {
        return $this->profileUrl;
    }

    public function jobTitle(): string
    {
        return $this->jobTitle;
    }

    public function employer(): string
    {
        return $this->employer;
    }

    public function employerUrl(): string
    {
        return $this->employerUrl;
    }

    public function education(): string
    {
        return $this->education;
    }

    public function expertise(): array
    {
        return $this->expertise;
    }

    public function primaryExpertise(): string
    {
        return $this->expertise[0] ?? '';
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function publicEmail(): string
    {
        return $this->publicEmail;
    }

    public function optsOutOfContactCard(): bool
    {
        return $this->vcardOptOut;
    }

    public function hasContactDetails(): bool
    {
        return trim($this->phone) !== ''
            || trim($this->publicEmail) !== ''
            || trim($this->street) !== ''
            || trim($this->locality) !== ''
            || trim($this->country) !== '';
    }

    public function street(): string
    {
        return $this->street;
    }

    public function locality(): string
    {
        return $this->locality;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function photoUrl(int $size = self::AVATAR_SIZE): string
    {
        if ($this->isGuest) {
            return $this->guestAvatarUrl;
        }

        if ($this->photoId === 0) {
            return '';
        }

        $url = wp_get_attachment_image_url($this->photoId, [$size, $size]);

        return is_string($url) ? $url : '';
    }

    public function hasPhoto(): bool
    {
        return $this->photoUrl() !== '';
    }

    public function photoId(): int
    {
        return $this->photoId;
    }

    public function gravatarUrl(int $size = self::AVATAR_SIZE): string
    {
        if ($this->isGuest) {
            return '';
        }

        $url = get_avatar_url($this->userId, ['size' => $size]);

        return is_string($url) ? $url : '';
    }

    public function avatarUrl(int $size = self::AVATAR_SIZE): string
    {
        $photo = $this->photoUrl($size);

        return $photo !== '' ? $photo : $this->gravatarUrl($size);
    }

    public function hasAvatar(): bool
    {
        return $this->avatarUrl() !== '';
    }

    public function estYear(): string
    {
        return $this->estYear;
    }

    public function statusLabel(): string
    {
        return $this->statusLabel;
    }

    public function ctaLabel(): string
    {
        return $this->ctaLabel;
    }

    public function ctaUrl(): string
    {
        return $this->ctaUrl;
    }

    public function socialProfiles(): array
    {
        return $this->socialProfiles;
    }

    public function schemaId(): string
    {
        return rtrim($this->profileUrl, '#') . self::SCHEMA_ANCHOR;
    }

    public function initials(): string
    {
        $first = self::firstLetterOf($this->firstName);
        $last  = self::firstLetterOf($this->lastName);

        if ($first !== '' && $last !== '') {
            return $first . $last;
        }

        $words = preg_split('/\s+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === false || $words === []) {
            return $first . $last;
        }

        if (count($words) === 1) {
            return self::firstLetterOf($words[0]);
        }

        return self::firstLetterOf($words[0]) . self::firstLetterOf($words[count($words) - 1]);
    }

    private static function firstLetterOf(string $value): string
    {
        $value = trim($value);

        return $value === '' ? '' : mb_strtoupper(mb_substr($value, 0, 1));
    }

    private static function metaString(int $userId, string $key): string
    {
        $value = get_user_meta($userId, $key, true);

        return is_string($value) ? trim($value) : '';
    }

    private static function toTermList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        $terms = [];

        foreach ($value as $term) {
            if (! is_string($term)) {
                continue;
            }

            $term = trim($term);

            if ($term !== '') {
                $terms[] = $term;
            }
        }

        return $terms;
    }

    private static function toUrlList(mixed $value): array
    {
        if (is_string($value)) {
            $split = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
            $value = $split === false ? [] : $split;
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }

    private static function toFoundingYear(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d{4}$/', $value) === 1 ? $value : '';
    }

    private static function buildHandle(string $explicit, string $nicename): string
    {
        $explicit = trim($explicit);

        if ($explicit !== '') {
            return '@' . ltrim($explicit, '@');
        }

        $nicename = trim($nicename);

        return $nicename === '' ? '' : '@' . $nicename;
    }

    private static function assertProfileUrl(string $profileUrl): void
    {
        if (trim($profileUrl) === '') {
            throw new InvalidArgumentException(
                'An author requires a profile URL; it anchors the schema @id.'
            );
        }
    }
}
