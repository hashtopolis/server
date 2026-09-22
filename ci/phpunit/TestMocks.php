<?php

namespace Hashtopolis\inc\templating {
  class Template {
    private string $name;
    
    public function __construct(string $name) {
      $this->name = $name;
    }
    
    public function render($data): string {
      return $this->name . ':' . json_encode($data);
    }
  }
}

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

  if (!function_exists(__NAMESPACE__ . '\\fopen')) {
    function fopen(string $filename, string $mode) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$filename, $mode], static function (string $filename, string $mode) {
        return \fopen($filename, $mode);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\feof')) {
    function feof($stream) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$stream], static function ($stream) {
        return \feof($stream);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\fread')) {
    function fread($stream, int $length) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$stream, $length], static function ($stream, int $length) {
        return \fread($stream, $length);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\fwrite')) {
    function fwrite($stream, string $data, ?int $length = null) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$stream, $data, $length], static function ($stream, string $data, ?int $length = null) {
        if ($length === null) {
          return \fwrite($stream, $data);
        }

        return \fwrite($stream, $data, $length);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\fclose')) {
    function fclose($stream) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$stream], static function ($stream) {
        return \fclose($stream);
      });
    }
  }
}

namespace Hashtopolis\inc\utils {
  if (!function_exists(__NAMESPACE__ . '\\file_exists')) {
    function file_exists(string $path) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$path], static function (string $path) {
        return \file_exists($path);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\unlink')) {
    function unlink(string $path) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$path], static function (string $path) {
        return \unlink($path);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\rename')) {
    function rename(string $from, string $to) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$from, $to], static function (string $from, string $to) {
        return \rename($from, $to);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_init')) {
    function curl_init(?string $url = null) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$url], static function (?string $url = null) {
        return $url === null ? \curl_init() : \curl_init($url);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_setopt_array')) {
    function curl_setopt_array($handle, array $options) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$handle, $options], static function ($handle, array $options) {
        return \curl_setopt_array($handle, $options);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_setopt')) {
    function curl_setopt($handle, $option, $value) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$handle, $option, $value], static function ($handle, $option, $value) {
        return \curl_setopt($handle, $option, $value);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_exec')) {
    function curl_exec($handle) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$handle], static function ($handle) {
        return \curl_exec($handle);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_getinfo')) {
    function curl_getinfo($handle, int $opt = 0) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$handle, $opt], static function ($handle, int $opt = 0) {
        return \curl_getinfo($handle, $opt);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\curl_close')) {
    function curl_close($handle) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$handle], static function ($handle) {
        return \curl_close($handle);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\hash_file')) {
    function hash_file(string $algo, string $filename, bool $binary = false) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$algo, $filename, $binary], static function (string $algo, string $filename, bool $binary = false) {
        return \hash_file($algo, $filename, $binary);
      });
    }
  }

  if (!function_exists(__NAMESPACE__ . '\\file_get_contents')) {
    function file_get_contents(string $filename, bool $use_include_path = false, $context = null, int $offset = 0, ?int $length = null) {
      return \hashtopolis_invoke_test_mock(__FUNCTION__, [$filename, $use_include_path, $context, $offset, $length], static function (string $filename, bool $use_include_path = false, $context = null, int $offset = 0, ?int $length = null) {
        if ($length === null) {
          return \file_get_contents($filename, $use_include_path, $context, $offset);
        }

        return \file_get_contents($filename, $use_include_path, $context, $offset, $length);
      });
    }
  }  
}