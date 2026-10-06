<?php

namespace GHOSTLABS\UNDERCARD;

use Exception;
use GHOSTLABS\UNDERCARD\Provider\AdminResourcesProvider;
use GHOSTLABS\UNDERCARD\Provider\AuthorRoutesProvider;
use GHOSTLABS\UNDERCARD\Provider\BlocksProvider;
use GHOSTLABS\UNDERCARD\Provider\BylineProvider;
use GHOSTLABS\UNDERCARD\Provider\ContactDownloadProvider;
use GHOSTLABS\UNDERCARD\Provider\FeatureProviderInterface;
use GHOSTLABS\UNDERCARD\Provider\GuestAuthorsProvider;
use GHOSTLABS\UNDERCARD\Provider\InstallerProvider;
use GHOSTLABS\UNDERCARD\Provider\PluginMetaProvider;
use GHOSTLABS\UNDERCARD\Provider\ProfileFieldsProvider;
use GHOSTLABS\UNDERCARD\Provider\SchemaPreviewProvider;
use GHOSTLABS\UNDERCARD\Provider\SiteSettingsProvider;
use GHOSTLABS\UNDERCARD\Service\Installer;
use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\OptionsService;
use GHOSTLABS\UNDERCARD\Service\PluginIdentity;
use GHOSTLABS\UNDERCARD\Service\PostAuthors;
use GHOSTLABS\UNDERCARD\Service\SiteSettings;
use GHOSTLABS\UNDERCARD\Traits\Singleton;
use GhostLabs\WPKit\Contracts\PluginIdentityInterface;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) exit;

class Kernel
{
    use Singleton;

    protected Loader $loader;

    protected PostAuthors $postAuthors;
    protected OptionsService $optionsService;
    protected wpdb $db;

    protected PluginIdentityInterface $identity;

    public function init(): static
    {
        $this->bootCollaborators();

        foreach ($this->providers() as $provider) {
            $provider->register($this->loader);
        }

        return $this;
    }

    protected function bootCollaborators(): void
    {
        
        $wpdb = $GLOBALS['wpdb'];

        $this->db             = $wpdb;
        $this->loader         = new Loader();
        $this->postAuthors    = new PostAuthors($wpdb);
        $this->optionsService = new OptionsService();
        $this->identity       = new PluginIdentity();
    }

    protected function providers(): array
    {
        return [

            new SiteSettingsProvider(new SiteSettings($this->optionsService)),
            new BlocksProvider(),
            new AdminResourcesProvider($this->identity),
            new AuthorRoutesProvider(),
            new ProfileFieldsProvider(),
            new ContactDownloadProvider(),
            new BylineProvider($this->postAuthors),
            new GuestAuthorsProvider(),
            new InstallerProvider(new Installer($this->db)),

            new PluginMetaProvider(),

            new SchemaPreviewProvider(),
        ];
    }

    public function run(): void
    {
        $this->loader->run();
    }
}
