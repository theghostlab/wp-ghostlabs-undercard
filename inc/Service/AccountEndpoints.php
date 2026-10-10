<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

final class AccountEndpoints
{
    
    private const BACKEND_BASE = 'https://enter.theghostlab.io';

    private const ACCOUNT_PATH = '/account';

    public static function accountUrl(): string
    {
        return self::BACKEND_BASE . self::ACCOUNT_PATH;
    }
}
