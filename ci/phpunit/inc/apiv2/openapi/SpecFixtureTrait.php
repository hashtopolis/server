<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Fixture comparison helper for generated OpenAPI specs.
 *
 * Fixtures live in ci/phpunit/fixtures/openapi/ and are pretty-printed JSON.
 * To (re)generate them, run the tests with UPDATE_OPENAPI_FIXTURES=1; the
 * test then writes the fixture and fails on purpose, so a CI run with the
 * variable set can never silently pass.
 */
trait SpecFixtureTrait {
  private function assertMatchesJsonFixture(array $actual, string $fixtureName): void {
    $fixtureFile = __DIR__ . '/../../../fixtures/openapi/' . $fixtureName;
    $actualJson = json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";

    if (getenv('UPDATE_OPENAPI_FIXTURES')) {
      if (!is_dir(dirname($fixtureFile))) {
        mkdir(dirname($fixtureFile), 0777, true);
      }
      file_put_contents($fixtureFile, $actualJson);
      $this->fail("Fixture '$fixtureName' regenerated; rerun the tests without UPDATE_OPENAPI_FIXTURES.");
    }

    $this->assertFileExists($fixtureFile, "Missing fixture '$fixtureName'; generate it by running the tests with UPDATE_OPENAPI_FIXTURES=1.");
    $this->assertSame(
      file_get_contents($fixtureFile),
      $actualJson,
      "Generated spec differs from fixture '$fixtureName'. If the change is intended, regenerate with UPDATE_OPENAPI_FIXTURES=1."
    );
  }
}
