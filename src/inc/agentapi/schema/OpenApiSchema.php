<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\schema;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agentapi\common\ActionRegistry;
use Hashtopolis\inc\StartupConfig;

/**
 * Generates an OpenAPI 3.1 document for the agent API from
 * {@see ActionRegistry} and {@see SchemaRegistry}.
 *
 * The agent API is a single-endpoint API: all requests are
 * ``POST /api/server.php`` with an ``action`` field in the JSON body that
 * determines the request/response shape.  The OpenAPI document represents
 * this with:
 *
 * - One path (``POST /api/server.php``) with one operation.
 * - Request body: ``oneOf`` of per-action request schemas, discriminated by
 *   the ``action`` field.
 * - Response 200: ``oneOf`` of per-action success schemas + the error
 *   envelope + the ``INV`` envelope.
 * - A ``components/schemas`` section with reusable per-action schemas.
 */
final class OpenApiSchema {
    /**
     * Build the complete OpenAPI 3.1 document as an associative array.
     *
     * @return array<string, mixed>
     */
    public static function generate(): array {
        $schemas = SchemaRegistry::getSchema();
        $actions = ActionRegistry::getActions();

        $components = [];
        $requestRefs = [];
        $responseRefs = [];

        foreach ($actions as $action) {
            $schema = $schemas[$action] ?? null;
            if ($schema === null) {
                continue;
            }

            $reqName = self::schemaName($action, 'Request');
            $respName = self::schemaName($action, 'Response');

            $components[$reqName] = self::buildRequestSchema($action, $schema['request'] ?? []);
            $components[$respName] = self::buildResponseSchema($schema['response'] ?? [], $schema['response_variants'] ?? []);

            $requestRefs[] = ['$ref' => "#/components/schemas/$reqName"];
            $responseRefs[] = ['$ref' => "#/components/schemas/$respName"];
        }

        $components['ErrorEnvelope'] = self::buildErrorEnvelopeSchema();
        $components['InvEnvelope'] = self::buildInvEnvelopeSchema();

        $responseRefs[] = ['$ref' => '#/components/schemas/ErrorEnvelope'];
        $responseRefs[] = ['$ref' => '#/components/schemas/InvEnvelope'];

        $version = StartupConfig::getInstance()->getVersion();

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Hashtopolis Agent API',
                'version' => $version,
                'description' => 'Agent communication protocol. All requests are POST /api/server.php with a JSON body containing an "action" field that determines the request and response shape.',
            ],
            'paths' => [
                '/api/server.php' => [
                    'post' => [
                        'summary' => 'Agent API endpoint',
                        'description' => 'Single-endpoint API. The "action" field in the request body selects one of 19 actions. Each action has its own request fields and response fields. Unknown or missing actions return the INV error envelope.',
                        'operationId' => 'agentApi',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'oneOf' => $requestRefs,
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'JSON response with at least "action" and "response" fields.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'oneOf' => $responseRefs,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => $components,
            ],
        ];
    }

    /**
     * Generate the OpenAPI document as a JSON string.
     */
    public static function generateJson(): string {
        return json_encode(self::generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Build the per-action request schema.
     *
     * @param string $action
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    private static function buildRequestSchema(string $action, array $fields): array {
        $properties = [
            PQuery::ACTION => [
                'type' => 'string',
                'enum' => [$action],
                'description' => 'The action to perform.',
            ],
        ];
        $required = [PQuery::ACTION];

        foreach ($fields as $name => $def) {
            $properties[$name] = self::buildPropertySchema($def);
            if ($def['required'] ?? false) {
                $required[] = $name;
            }
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * Build the per-action success response schema (extends the base
     * success envelope with action-specific fields).
     *
     * @param array<string, array<string, mixed>> $fields
     * @param list<array<string, mixed>> $variants
     * @return array<string, mixed>
     */
    private static function buildResponseSchema(array $fields, array $variants = []): array {
        $properties = [
            PResponse::ACTION   => ['type' => 'string', 'description' => 'The action that was performed.'],
            PResponse::RESPONSE => ['type' => 'string', 'enum' => [PValues::SUCCESS], 'description' => 'Always "SUCCESS" for success responses.'],
        ];
        $required = [PResponse::ACTION, PResponse::RESPONSE];

        foreach ($fields as $name => $def) {
            $properties[$name] = self::buildPropertySchema($def);
        }

        foreach ($variants as $variant) {
            foreach ($variant['response'] ?? [] as $name => $def) {
                $properties[$name] = self::buildPropertySchema($def);
            }
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * Build the standard error envelope schema.
     *
     * @return array<string, mixed>
     */
    private static function buildErrorEnvelopeSchema(): array {
        return [
            'type' => 'object',
            'title' => 'Error response',
            'description' => 'Returned when an action-specific error occurs (e.g. invalid token, invalid task, etc.).',
            'properties' => [
                PResponse::ACTION             => ['type' => 'string', 'description' => 'The action that was requested.'],
                PResponse::RESPONSE           => ['type' => 'string', 'enum' => [PValues::ERROR], 'description' => 'Always "ERROR".'],
                PResponseErrorMessage::MESSAGE => ['type' => 'string', 'description' => 'Human-readable error message.'],
            ],
            'required' => [PResponse::ACTION, PResponse::RESPONSE, PResponseErrorMessage::MESSAGE],
        ];
    }

    /**
     * Build the INV error envelope schema (unknown / missing action).
     *
     * @return array<string, mixed>
     */
    private static function buildInvEnvelopeSchema(): array {
        return [
            'type' => 'object',
            'title' => 'Invalid query response',
            'description' => 'Returned when the action is unknown or missing.',
            'properties' => [
                PResponse::ACTION             => ['type' => 'string', 'enum' => ['INV'], 'description' => 'Always "INV".'],
                PResponse::RESPONSE           => ['type' => 'string', 'enum' => [PValues::ERROR], 'description' => 'Always "ERROR".'],
                PResponseErrorMessage::MESSAGE => ['type' => 'string', 'description' => 'Always "Invalid query!".'],
            ],
            'required' => [PResponse::ACTION, PResponse::RESPONSE, PResponseErrorMessage::MESSAGE],
        ];
    }

    /**
     * Convert a SchemaRegistry field definition to an OpenAPI property schema.
     *
     * @param array<string, mixed> $def
     * @return array<string, mixed>
     */
    private static function buildPropertySchema(array $def): array {
        $type = $def['type'] ?? 'string';
        $schema = [];

        if (str_starts_with($type, 'array<')) {
            $itemType = substr($type, 6, -1);
            $schema['type'] = 'array';
            $schema['items'] = self::primitiveSchema($itemType);
        }
        else {
            $schema = self::primitiveSchema($type);
        }

        if (isset($def['description'])) {
            $schema['description'] = $def['description'];
        }
        if (isset($def['enum'])) {
            $schema['enum'] = $def['enum'];
        }

        return $schema;
    }

    /**
     * Map a primitive type string to an OpenAPI schema fragment.
     *
     * @return array<string, string>
     */
    private static function primitiveSchema(string $type): array {
        return match ($type) {
            'integer' => ['type' => 'integer'],
            'boolean' => ['type' => 'boolean'],
            'number'  => ['type' => 'number'],
            'array'   => ['type' => 'array', 'items' => ['type' => 'string']],
            default   => ['type' => 'string'],
        };
    }

    /**
     * Generate a PascalCase schema component name from an action string.
     *
     * @param string $action  e.g. "sendKeyspace"
     * @param string $suffix  "Request" or "Response"
     * @return string  e.g. "SendKeyspaceRequest"
     */
    private static function schemaName(string $action, string $suffix): string {
        $parts = explode(' ', str_replace('-', ' ', preg_replace('/([A-Z])/', ' $1', $action) ?? $action));
        $name = '';
        foreach ($parts as $part) {
            $name .= ucfirst($part);
        }
        return $name . $suffix;
    }
}
