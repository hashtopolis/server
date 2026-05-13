<?php

namespace {
	function hashtopolis_set_test_mock(string $name, callable $mock): void {
		$GLOBALS['hashtopolis_test_mocks'][$name] = $mock;
	}

	function hashtopolis_clear_test_mocks(?array $names = null): void {
		if ($names === null) {
			unset($GLOBALS['hashtopolis_test_mocks']);
			return;
		}

		foreach ($names as $name) {
			unset($GLOBALS['hashtopolis_test_mocks'][$name]);
		}

		if (empty($GLOBALS['hashtopolis_test_mocks'])) {
			unset($GLOBALS['hashtopolis_test_mocks']);
		}
	}

	function hashtopolis_invoke_test_mock(string $name, array $args, callable $fallback) {
		if (isset($GLOBALS['hashtopolis_test_mocks'][$name]) && is_callable($GLOBALS['hashtopolis_test_mocks'][$name])) {
			return $GLOBALS['hashtopolis_test_mocks'][$name](...$args);
		}

		return $fallback(...$args);
	}
}

namespace Hashtopolis\inc {
	if (!function_exists(__NAMESPACE__ . '\\is_file')) {
		function is_file($path) {
			return \hashtopolis_invoke_test_mock(__FUNCTION__, [$path], static function ($path) {
				return \is_file($path);
			});
		}
	}

	if (!function_exists(__NAMESPACE__ . '\\mail')) {
		function mail($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) {
			return \hashtopolis_invoke_test_mock(__FUNCTION__, [$to, $subject, $message, $additionalHeaders, $additionalParams], static function ($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) {
				if ($additionalParams === null) {
					return \mail($to, $subject, $message, $additionalHeaders ?? '');
				}

				return \mail($to, $subject, $message, $additionalHeaders ?? '', $additionalParams);
			});
		}
	}

	if (!function_exists(__NAMESPACE__ . '\\error_log')) {
		function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
			return \hashtopolis_invoke_test_mock(__FUNCTION__, [$message, $messageType, $destination, $additionalHeaders], static function ($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
				if ($destination === null) {
					return \error_log($message, $messageType);
				}

				if ($additionalHeaders === null) {
					return \error_log($message, $messageType, $destination);
				}

				return \error_log($message, $messageType, $destination, $additionalHeaders);
			});
		}
	}
}

namespace Hashtopolis\inc\notifications {
	if (!function_exists(__NAMESPACE__ . '\\error_log')) {
		function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
			return \hashtopolis_invoke_test_mock(__FUNCTION__, [$message, $messageType, $destination, $additionalHeaders], static function ($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
				if ($destination === null) {
					return \error_log($message, $messageType);
				}

				if ($additionalHeaders === null) {
					return \error_log($message, $messageType, $destination);
				}

				return \error_log($message, $messageType, $destination, $additionalHeaders);
			});
		}
	}
}

namespace Hashtopolis\inc\utils {
	if (!function_exists(__NAMESPACE__ . '\\error_log')) {
		function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
			return \hashtopolis_invoke_test_mock(__FUNCTION__, [$message, $messageType, $destination, $additionalHeaders], static function ($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
				if ($destination === null) {
					return \error_log($message, $messageType);
				}

				if ($additionalHeaders === null) {
					return \error_log($message, $messageType, $destination);
				}

				return \error_log($message, $messageType, $destination, $additionalHeaders);
			});
		}
	}
}
