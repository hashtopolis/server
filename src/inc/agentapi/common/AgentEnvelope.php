<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;

/**
 * Builds the wire-level response envelopes used by the agent API.
 *
 * Every response is a JSON object with at least ``action`` and ``response``
 * keys.  Success envelopes add action-specific fields; error envelopes add a
 * ``message`` key.
 */
final class AgentEnvelope {
    /**
     * Build a success envelope: ``{"action":<action>, "response":"SUCCESS", ...}``.
     *
     * @param string $action   The action string to echo back.
     * @param array<string,mixed> $fields  Additional fields to include.
     * @return array<string,mixed>
     */
    public static function success(string $action, array $fields = []): array {
        return array_merge(
            [PResponse::ACTION => $action, PResponse::RESPONSE => PValues::SUCCESS],
            $fields,
        );
    }

    /**
     * Build an error envelope: ``{"action":<action>, "response":"ERROR", "message":<msg>}``.
     *
     * @param string $action   The action string to echo back (or ``"INV"`` for
     *                         unknown / missing actions).
     * @param string $message  The error message.
     * @return array<string,mixed>
     */
    public static function error(string $action, string $message): array {
        return [
            PResponse::ACTION        => $action,
            PResponse::RESPONSE      => PValues::ERROR,
            PResponseErrorMessage::MESSAGE => $message,
        ];
    }
}
