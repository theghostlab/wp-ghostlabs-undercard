<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\ProfileFields;
use wpdb;

final class Installer
{
    
    public const SCHEMA_VERSION = '2';

    private const LEGACY_PREFIX = 'gl_';

    private const VERSION_OPTION = 'ghostlabs_undercard_schema_version';

    public function __construct(private readonly wpdb $db)
    {
    }

    public function register(): void
    {
        add_action('init', [$this, 'maybeInstall']);
    }

    public function maybeInstall(): void
    {
        $installed = get_option(self::VERSION_OPTION);

        if (is_string($installed) && $installed === self::SCHEMA_VERSION) {
            return;
        }

        if (! function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta($this->schema());
        $this->renameLegacyMetaKeys();

        update_option(self::VERSION_OPTION, self::SCHEMA_VERSION);
    }

    private function renameLegacyMetaKeys(): void
    {
        foreach ([$this->db->usermeta, $this->db->postmeta] as $table) {
            foreach (array_keys(ProfileFields::all()) as $key) {
                $legacy = self::LEGACY_PREFIX . substr($key, strlen('undercard_'));

                $sql = $this->db->prepare(
                    'UPDATE %i SET meta_key = %s WHERE meta_key = %s',
                    $table,
                    $key,
                    $legacy
                );

                if (is_string($sql)) {
                    $this->db->query($sql);
                }
            }
        }
    }

    public function schema(): string
    {
        $table   = PostAuthors::tableName($this->db);
        $collate = $this->db->get_charset_collate();

        return "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            author_id varchar(191) NOT NULL,
            position smallint(5) unsigned NOT NULL DEFAULT 0,
            role varchar(32) NOT NULL DEFAULT 'author',
            PRIMARY KEY  (id),
            UNIQUE KEY post_author (post_id,author_id),
            KEY post_position (post_id,position),
            KEY author_lookup (author_id)
        ) {$collate};";
    }
}
