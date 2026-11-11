<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\dba\models\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * The per-model corrections applied to a derived attributes schema.
 */
final class SpecOverridesTest extends TestCase {
  private function attributesSchema(): array {
    return [
      "type" => "object",
      "required" => ["name", "email", "isValid", "state"],
      "properties" => [
        "name" => ["type" => "string"],
        "email" => ["type" => "string"],
        "isValid" => ["type" => "boolean"],
        "state" => [
          "type" => "integer",
          "enum" => [0, 1],
          "description" => "One of:\n\n* `0` - Off\n* `1` - On",
        ],
      ],
    ];
  }

  public function testAModelWithoutOverridesKeepsItsSchema(): void {
    $schema = $this->attributesSchema();
    $this->assertSame($schema, (new SpecOverrides())->apply('Foo', $schema));
    $overrides = new SpecOverrides(['Bar' => [SpecOverrides::OPTIONAL_ATTRIBUTES => ['email']]]);
    $this->assertSame($schema, $overrides->apply('Foo', $schema));
    $this->assertFalse($overrides->has('Foo'));
    $this->assertTrue($overrides->has('Bar'));
  }

  public function testOptionalAttributesLeaveTheRequiredList(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::OPTIONAL_ATTRIBUTES => ['email', 'isValid']],
    ]);
    $result = $overrides->apply('Foo', $this->attributesSchema());

    /* Dropped from "required", but still described and still not nullable */
    $this->assertSame(["name", "state"], $result['required']);
    $this->assertSame(["type" => "string"], $result['properties']['email']);
    $this->assertSame(["type" => "boolean"], $result['properties']['isValid']);
  }

  public function testNullableAttributesStayRequiredAndGainTheNullType(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::NULLABLE_ATTRIBUTES => ['email', 'state']],
    ]);
    $result = $overrides->apply('Foo', $this->attributesSchema());

    $this->assertSame(["name", "email", "isValid", "state"], $result['required']);
    $this->assertSame(["type" => ["string", "null"]], $result['properties']['email']);
    /* A value the property accepts has to be in its enum too, or nothing can satisfy the schema */
    $this->assertSame(["integer", "null"], $result['properties']['state']['type']);
    $this->assertSame([0, 1, null], $result['properties']['state']['enum']);
  }

  public function testTheTwoCorrectionsCombinePerAttribute(): void {
    $overrides = new SpecOverrides([
      'Foo' => [
        SpecOverrides::OPTIONAL_ATTRIBUTES => ['email'],
        SpecOverrides::NULLABLE_ATTRIBUTES => ['email', 'isValid'],
      ],
    ]);
    $result = $overrides->apply('Foo', $this->attributesSchema());

    /* email may be absent and null, isValid only null */
    $this->assertSame(["name", "isValid", "state"], $result['required']);
    $this->assertSame(["type" => ["string", "null"]], $result['properties']['email']);
    $this->assertSame(["type" => ["boolean", "null"]], $result['properties']['isValid']);
  }

  public function testAnAlreadyNullableAttributeIsNotWidenedTwice(): void {
    $schema = [
      "type" => "object",
      "required" => ["comment", "state"],
      "properties" => [
        "comment" => ["type" => ["string", "null"]],
        "state" => ["oneOf" => [["const" => 1, "title" => "On", "type" => "integer"], ["type" => "null"]]],
      ],
    ];
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::NULLABLE_ATTRIBUTES => ['comment', 'state']],
    ]);

    $this->assertSame($schema, $overrides->apply('Foo', $schema));
  }

  public function testAnUnknownCorrectionIsRejected(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("Unknown spec override 'requiredAttributes' for 'Foo'");
    new SpecOverrides(['Foo' => ['requiredAttributes' => ['email']]]);
  }

  public function testCorrectionsMustNameAttributes(): void {
    $this->expectException(InvalidArgumentException::class);
    new SpecOverrides(['Foo' => [SpecOverrides::OPTIONAL_ATTRIBUTES => [42]]]);
  }

  /**
   * A correction naming an attribute the model does not carry is a typo or a
   * rename that was not followed, and would otherwise pass unnoticed.
   */
  public function testACorrectionForAnAbsentAttributeIsRejected(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::OPTIONAL_ATTRIBUTES => ['emial']],
    ]);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("names attribute 'emial', which the model does not have");
    $overrides->apply('Foo', $this->attributesSchema());
  }

  /**
   * The defaults exist because obj2Resource keeps only the 'public' features
   * once the caller lacks the read permission of the model, so they have to
   * cover every other User attribute. A new User column has to be classified
   * here rather than silently claimed to be always present.
   */
  public function testTheDefaultsCoverEveryNonPublicUserAttribute(): void {
    $filtered = [];
    foreach (User::getFeatures() as $feature) {
      if ($feature['pk'] || $feature['private'] || $feature['public']) {
        continue;
      }
      $filtered[] = $feature['alias'];
    }
    sort($filtered);

    $schema = [
      "type" => "object",
      "required" => $filtered,
      "properties" => array_fill_keys($filtered, ["type" => "string"]),
    ];
    $result = SpecOverrides::defaults()->apply('User', $schema);

    $this->assertSame([], $result['required'], "Every attribute a permission-filtered user response omits must be optional");
  }

  /**
   * Only User carries 'public' features, so it is the only model whose
   * responses arrive attribute-filtered and needs a correction here at all.
   * Documentation lives on the features instead, where it reaches every
   * schema rather than only the response and the create request.
   */
  public function testTheDefaultsContainTheExpectedModels(): void {
    $this->assertTrue(SpecOverrides::defaults()->has('User'));
    foreach (['Agent', 'Task', 'Hashlist', 'Config', 'ApiToken', 'CrackerBinary'] as $model) {
      $this->assertFalse(SpecOverrides::defaults()->has($model), $model);
    }
  }

  /**
   * The description of an attribute limited to a set of values carries the
   * meaning of each value, which replacing the text would silently drop, so
   * such an attribute has to be described where its values are declared.
   */
  public function testDescribingAnEnumeratedAttributeIsRefused(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::ATTRIBUTE_DESCRIPTIONS => ['state' => 'The state']],
    ]);
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("limited to a set of values");
    $overrides->apply('Foo', $this->attributesSchema());
  }

  public function testAttributeDescriptionsAreAdded(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::ATTRIBUTE_DESCRIPTIONS => [
        'email' => 'The email address',
        'isValid' => 'Whether the account may sign in',
      ]],
    ]);
    $result = $overrides->apply('Foo', $this->attributesSchema());

    $this->assertSame('The email address', $result['properties']['email']['description']);
    $this->assertSame('Whether the account may sign in', $result['properties']['isValid']['description']);
    /* purely additive, the required list and the types stay as they were */
    $this->assertSame(["name", "email", "isValid", "state"], $result['required']);
    $this->assertSame(["type" => "string"], ['type' => $result['properties']['email']['type']]);
  }

  /**
   * Unlike the optional/nullable corrections, a description for an attribute
   * which is not part of the schema shape is simply skipped: the same
   * description set is applied to responses and requests, and creation-only
   * form fields for example are not part of a response.
   */
  public function testAttributeDescriptionsSkipAbsentProperties(): void {
    $overrides = new SpecOverrides([
      'Foo' => [SpecOverrides::ATTRIBUTE_DESCRIPTIONS => ['sourceType' => 'Upload source']],
    ]);
    $result = $overrides->apply('Foo', $this->attributesSchema());

    $this->assertSame($this->attributesSchema(), $result);
  }

  /**
   * applyDescriptions() serves the create request schema: descriptions are
   * added, but the response oriented optional/nullable corrections must not
   * be applied to it.
   */
  public function testApplyDescriptionsIgnoresTheOtherCorrections(): void {
    $overrides = new SpecOverrides([
      'Foo' => [
        SpecOverrides::OPTIONAL_ATTRIBUTES => ['email'],
        SpecOverrides::NULLABLE_ATTRIBUTES => ['isValid'],
        SpecOverrides::ATTRIBUTE_DESCRIPTIONS => ['email' => 'The email address'],
      ],
    ]);
    $result = $overrides->applyDescriptions('Foo', $this->attributesSchema());

    $this->assertSame('The email address', $result['properties']['email']['description']);
    $this->assertSame(["name", "email", "isValid", "state"], $result['required']);
    $this->assertSame('string', $result['properties']['email']['type']);
    $this->assertSame('boolean', $result['properties']['isValid']['type']);
  }

  public function testDescriptionsMustMapToNonEmptyStrings(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("must map attribute names to non-empty descriptions");
    new SpecOverrides(['Foo' => [SpecOverrides::ATTRIBUTE_DESCRIPTIONS => ['email' => '']]]);
  }

  public function testDescriptionsMustNameAttributes(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("must map attribute names to non-empty descriptions");
    new SpecOverrides(['Foo' => [SpecOverrides::ATTRIBUTE_DESCRIPTIONS => [42 => 'desc']]]);
  }
}
