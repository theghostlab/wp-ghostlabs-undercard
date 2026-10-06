<?php

namespace GHOSTLABS\UNDERCARD\Config;

use GHOSTLABS\UNDERCARD\Enum\FieldType;

final class ProfileFields
{
    
    public static function all(): array
    {
        static $fields = null;

        if ($fields !== null) {
            return $fields;
        }

        $definitions = [
            new ProfileField(
                'undercard_avatar_id',
                'Profile photo',
                FieldType::Attachment,
                'Used before Gravatar. Without either, cards fall back to your initials.',
            ),
            new ProfileField(
                'undercard_job_title',
                'Job title',
                FieldType::Text,
                'Shown on the card and emitted as jobTitle.',
            ),
            new ProfileField(
                'undercard_employer',
                'Employer',
                FieldType::Text,
                'Emitted as worksFor.',
            ),
            new ProfileField('undercard_employer_url', 'Employer website', FieldType::Url),
            new ProfileField(
                'undercard_education',
                'Education',
                FieldType::Text,
                'Where you studied. Emitted as alumniOf. No card renders it yet.',
            ),
            new ProfileField(
                'undercard_est_year',
                'Employer founded',
                FieldType::Year,
                'Four-digit year. Emitted as worksFor.foundingDate.',
            ),
            new ProfileField(
                'undercard_expertise',
                'Areas of expertise',
                FieldType::Terms,
                'Comma separated. The first term appears on the card; all of them are emitted as knowsAbout.',
            ),
            new ProfileField(
                'undercard_handle',
                'Handle',
                FieldType::Text,
                'Defaults to your profile slug if left blank.',
            ),
            new ProfileField(
                'undercard_status_label',
                'Availability badge',
                FieldType::Text,
                'Optional, for example "Available for work". Hidden when blank.',
            ),
            new ProfileField('undercard_street', 'Street address', FieldType::Text),
            new ProfileField('undercard_locality', 'City', FieldType::Text),
            new ProfileField('undercard_country', 'Country', FieldType::Text),

            new ProfileField(
                'undercard_cta_label',
                'Call to action label',
                FieldType::Text,
                'Optional. Defaults to "View posts"; with a link below and no label here, "Learn more".',
            ),
            new ProfileField(
                'undercard_cta_url',
                'Call to action link',
                FieldType::Url,
                'Optional. Sends the card\'s button somewhere other than the author archive.',
            ),
            new ProfileField(
                'undercard_social',
                'Social profiles',
                FieldType::Urls,
                'One URL per line. Shown as links and emitted as sameAs.',
            ),
            new ProfileField(
                'undercard_phone',
                'Public phone',
                FieldType::Text,
                isContactMethod: true,
            ),
            new ProfileField(
                'undercard_public_email',
                'Public email',
                FieldType::Email,
                'Never your account email. Leave blank to publish no address.',
                isContactMethod: true,
            ),
            new ProfileField(
                'undercard_website',
                'Website',
                FieldType::Url,
                'Emitted as url.',
                isGuestOnly: true,
            ),
            new ProfileField(
                'undercard_vcard_optout',
                'Hide my contact download',
                FieldType::Boolean,
                'Stops the .vcf download being served for you. The card still shows'
                    . ' whatever you have filled in above — this only withdraws the'
                    . ' downloadable file.',
                isUserOnly: true,
            ),
        ];

        $fields = [];

        foreach ($definitions as $field) {
            $fields[$field->key] = $field;
        }

        return $fields;
    }

    public static function get(string $key): ?ProfileField
    {
        return self::all()[$key] ?? null;
    }

    public static function forUsers(): array
    {
        return array_filter(
            self::all(),
            static fn(ProfileField $field): bool => ! $field->isGuestOnly
        );
    }

    public static function forGuests(): array
    {
        return array_filter(
            self::all(),
            static fn(ProfileField $field): bool => ! $field->isUserOnly
        );
    }

    public static function contactMethods(): array
    {
        return array_filter(
            self::all(),
            static fn(ProfileField $field): bool => $field->isContactMethod
        );
    }

    public static function ownSection(): array
    {
        return array_filter(
            self::forUsers(),
            static fn(ProfileField $field): bool => ! $field->isContactMethod
        );
    }
}
