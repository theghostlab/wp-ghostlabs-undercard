<?php


namespace GHOSTLABS\UNDERCARD\RestRoute;

use Exception;
use GHOSTLABS\UNDERCARD\Contracts\PluginIdentityInterface;
use GHOSTLABS\UNDERCARD\Contracts\SaveSettingsHandlerInterface;
use GHOSTLABS\UNDERCARD\Service\DTOMapper;
use GHOSTLABS\UNDERCARD\Service\SaveSettings;
use GHOSTLABS\UNDERCARD\Service\Utils;
use RuntimeException;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class AbstractSettingsRoutes implements RestRouteInterface
{
    private Utils $utils;

    public function __construct(
        protected readonly PluginIdentityInterface $config,
    ) {
        $this->utils = new Utils();
    }

    abstract protected function dtoMap(): array;

    abstract protected function handlerMap(): array;

    public function routes(): void
    {
        $namespace = $this->config->getRestNamespace();

        if ($namespace === '' || $namespace === '0') {
            return;
        }

        register_rest_route($namespace, '/save-settings', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'saveSettings'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => $this->routeArgs(),
            ],
        ]);
    }

    protected function routeArgs(): array
    {
        return [
            'type' => [
                'required' => true,
                'type' => 'string',
                'enum' => array_keys($this->dtoMap()),
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    public function saveSettings(WP_REST_Request $request): WP_Error|WP_REST_Response|WP_HTTP_Response
    {
        
        $data = $request->get_json_params();

        $payload = $this->utils->setPayload($data);

        $dtoMap = $this->dtoMap();

        $type = $payload['type'] ?? '';
        $dtoConfig = $dtoMap[$type] ?? $dtoMap[array_key_last($dtoMap)];

        try {
            $dto = DTOMapper::map($payload, $dtoConfig[0]);

            $errors = DTOMapper::validate($dto, $dtoConfig[1]);

            if (!empty($errors)) {
                $errors = json_encode($errors) === false ? 'There was an error validating the data.' : json_encode($errors);

                throw new RuntimeException($errors, 412);
            }

            $saveSettings = new SaveSettings($this->handlerMap());
            $results = $saveSettings->setPayload((array)$dto)->run();

            $errors = [];
            $success = [];

            foreach ($results as $result) {
                $stack = $result;
                $stack = array_shift($stack);

                if (isset($stack['error'])) {
                    $errors = array_merge($errors, $result);
                } else {
                    $success = array_merge($success, $result);
                }
            }

            if (!empty($errors)) {
                $code = array_shift($errors)['code'] ?? 412;
                $errors = json_encode($errors) === false ? 'There was an error validating the data.' : json_encode($errors);
                throw new RuntimeException($errors, $code);
            }

            return rest_ensure_response([
                'status' => 200,
                'errors' => $errors,
                'success' => $success,
            ]);
        } catch (RuntimeException|Exception $e) {
            return rest_ensure_response(new WP_Error(
                $e->getCode(),
                $e->getMessage(),
                ['status' => $e->getCode(), 'errors' => json_decode($e->getMessage())]
            ));
        }
    }
}
