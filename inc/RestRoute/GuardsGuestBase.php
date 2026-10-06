<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\RestRoute;

use GHOSTLABS\UNDERCARD\Config\SettingsCopy;
use GHOSTLABS\UNDERCARD\Service\GuestBase;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

trait GuardsGuestBase
{
    public function saveSettings(WP_REST_Request $request): WP_Error|WP_REST_Response|WP_HTTP_Response
    {
        $data = $request->get_json_params();

        if (($data['type'] ?? '') === 'settings' && isset($data['guestSlug']) && is_string($data['guestSlug'])) {
            $slug  = GuestBase::normalize($data['guestSlug']);
            $owner = GuestBase::owner($slug);

            if ($owner !== null) {
                $errors = [ 'guestSlug' => [ SettingsCopy::guestBaseTaken($slug, $owner) ] ];

                return new WP_Error(
                    'ghostlabs_undercard_guest_base_taken',
                    (string) wp_json_encode($errors),
                    [ 'status' => 412, 'errors' => $errors ]
                );
            }
        }

        return parent::saveSettings($request);
    }
}
