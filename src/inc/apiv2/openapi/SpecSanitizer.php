<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Post-processes the raw generated spec for strict OpenAPI 3.1.0 compliance.
 */
class SpecSanitizer {
  public function sanitize(array $spec): array {
    // Fix: Add missing info fields
    if (!isset($spec['info']['description'])) {
      $spec['info']['description'] = 'Hashtopolis REST API';
    }
    if (!isset($spec['info']['contact'])) {
      $spec['info']['contact'] = [
        'name' => 'Hashtopolis',
        'url' => 'https://github.com/hashtopolis/server'
      ];
    }

    // Phase 1: Build rename map for schema names containing backslashes
    $renameMap = [];
    if (isset($spec['components']['schemas'])) {
      $usedShortNames = [];
      // Collect existing non-backslash names to avoid collisions
      foreach (array_keys($spec['components']['schemas']) as $key) {
        if (!str_contains($key, '\\')) {
          $usedShortNames[$key] = true;
        }
      }
      // Build rename map: extract short class name (last segment after \)
      foreach (array_keys($spec['components']['schemas']) as $key) {
        if (str_contains($key, '\\')) {
          $shortName = substr($key, strrpos($key, '\\') + 1);
          if (!isset($usedShortNames[$shortName])) {
            $renameMap[$key] = $shortName;
            $usedShortNames[$shortName] = true;
          }
        }
      }
      // Apply rename to component schema keys
      $newSchemas = [];
      foreach ($spec['components']['schemas'] as $key => $value) {
        $newKey = $renameMap[$key] ?? $key;
        $newSchemas[$newKey] = $value;
      }
      $spec['components']['schemas'] = $newSchemas;
    }

    // Phase 2: Remove scopes from bearerAuth (only valid on OAuth2)
    if (isset($spec['components']['securitySchemes']['bearerAuth']['scopes'])) {
      unset($spec['components']['securitySchemes']['bearerAuth']['scopes']);
    }

    // Phase 3: Clean path templates (strip Slim regex patterns)
    $newPaths = [];
    foreach ($spec['paths'] as $path => $pathItem) {
      $cleanPath = preg_replace('/\{([^:}]+):[^}]+\}/', '{$1}', $path);
      $newPaths[$cleanPath] = $pathItem;
    }
    $spec['paths'] = $newPaths;

    // Phase 4: Walk operations for fixes
    foreach ($spec['paths'] as $path => &$pathItem) {
      foreach ($pathItem as $method => &$operation) {
        if (!is_array($operation)) continue;

        // Fix: Security requirement - bearerAuth should have empty scopes array for HTTP bearer
        if (isset($operation['security'])) {
          foreach ($operation['security'] as &$secReq) {
            if (isset($secReq['bearerAuth'])) {
              $secReq['bearerAuth'] = [];
            }
          }
          unset($secReq);
        }

        // Fix: Query params incorrectly marked as path params + style casing
        if (isset($operation['parameters'])) {
          $queryParamNames = ['page[after]', 'page[before]', 'page[size]', 'filter', 'include'];
          foreach ($operation['parameters'] as &$param) {
            if (isset($param['in']) && $param['in'] === 'path' && in_array($param['name'], $queryParamNames)) {
              $param['in'] = 'query';
            }
            // Fix: style casing (deepobject -> deepObject)
            if (isset($param['style']) && $param['style'] === 'deepobject') {
              $param['style'] = 'deepObject';
            }
          }
          unset($param);
        }

        // Fix: requestBody as indexed array -- unwrap to first element
        if (isset($operation['requestBody'][0]) && is_array($operation['requestBody'][0])) {
          $operation['requestBody'] = $operation['requestBody'][0];
        }

        // Fix: Walk response content
        if (isset($operation['responses'])) {
          foreach ($operation['responses'] as &$responseObj) {
            if (!is_array($responseObj) || !isset($responseObj['content'])) continue;
            foreach ($responseObj['content'] as $mediaType => &$mediaObj) {
              // Fix: Empty media type object
              if (is_array($mediaObj) && empty($mediaObj)) {
                $mediaObj = ["schema" => ["type" => "object"]];
              }
              // Fix: Missing schema wrapper (has 'type' but no 'schema')
              elseif (is_array($mediaObj) && isset($mediaObj['type']) && !isset($mediaObj['schema'])) {
                $mediaObj = ["schema" => $mediaObj];
              }
            }
            unset($mediaObj);
          }
          unset($responseObj);
        }

        // Fix: Also for requestBody content
        if (isset($operation['requestBody']['content'])) {
          foreach ($operation['requestBody']['content'] as $mediaType => &$mediaObj) {
            if (is_array($mediaObj) && empty($mediaObj)) {
              $mediaObj = ["schema" => ["type" => "object"]];
            }
          }
          unset($mediaObj);
        }

        // Fix: Clean backslash-prefixed tag names
        if (isset($operation['tags'])) {
          $operation['tags'] = array_map(function($tag) {
            return str_contains($tag, '\\') ? substr($tag, strrpos($tag, '\\') + 1) : $tag;
          }, $operation['tags']);
        }
        // Fix: Add missing tags for helper/auth operations
        if (!isset($operation['tags']) || empty($operation['tags'])) {
          if (str_starts_with($path, '/api/v2/helper/')) {
            $operation['tags'] = ['Helpers'];
          } elseif (str_starts_with($path, '/api/v2/auth/')) {
            $operation['tags'] = ['Authentication'];
          }
        }

        // Fix: Add missing path parameter definitions
        preg_match_all('/\{(\w+)\}/', $path, $pathParamMatches);
        $expectedPathParams = $pathParamMatches[1] ?? [];
        if (!empty($expectedPathParams)) {
          $definedPathParams = [];
          if (isset($operation['parameters'])) {
            foreach ($operation['parameters'] as $existingParam) {
              if (isset($existingParam['in']) && $existingParam['in'] === 'path') {
                $definedPathParams[] = $existingParam['name'];
              }
            }
          }
          foreach ($expectedPathParams as $paramName) {
            if (!in_array($paramName, $definedPathParams)) {
              if (!isset($operation['parameters'])) {
                $operation['parameters'] = [];
              }
              $operation['parameters'][] = [
                "name" => $paramName,
                "in" => "path",
                "required" => true,
                "schema" => [
                  "type" => $paramName === 'id' ? "integer" : "string",
                ]
              ];
            }
          }
        }

        // Fix: Add missing operation summary
        if (!isset($operation['summary'])) {
          $tag = $operation['tags'][0] ?? '';
          $hasId = str_contains($path, '{id}');
          $isRelation = str_contains($path, '/relationships/');
          $isCount = str_ends_with($path, '/count');
          $summary = match($method) {
            'get' => $isCount ? "Count $tag" : ($hasId ? "Get $tag" : "List $tag"),
            'post' => $isRelation ? "Add $tag relationship" : "Create $tag",
            'patch' => "Update $tag",
            'delete' => $isRelation ? "Remove $tag relationship" : "Delete $tag",
            'head' => "Head $tag",
            default => ucfirst($method) . " $tag"
          };
          $operation['summary'] = $summary;
        }

        // Fix: Generate unique operationId
        if (!isset($operation['operationId'])) {
          $stripped = preg_replace('#^/api/v2/(ui|helper|auth)/#', '', $path);
          $parts = [];
          foreach (explode('/', $stripped) as $seg) {
            if ($seg === '') continue;
            if (str_starts_with($seg, '{')) {
              $parts[] = 'By' . ucfirst(trim($seg, '{}'));
            } else {
              $parts[] = ucfirst($seg);
            }
          }
          $operation['operationId'] = $method . implode('', $parts);
        }

        // Fix: Fill empty descriptions
        if (!isset($operation['description']) || $operation['description'] === '') {
          $operation['description'] = $operation['summary'] ?? '';
        }

        // Fix: Ensure operation has security defined
        if (!isset($operation['security'])) {
          $operation['security'] = [["bearerAuth" => []]];
        }

        // Fix: Ensure at least one 2xx response exists
        if (isset($operation['responses'])) {
          $has2xx = false;
          foreach (array_keys($operation['responses']) as $code) {
            if (str_starts_with((string)$code, '2')) {
              $has2xx = true;
              break;
            }
          }
          if (!$has2xx) {
            $operation['responses']['200'] = [
              "description" => "successful operation"
            ];
          }
        }
      }
      unset($operation);
    }
    unset($pathItem);

    // Phase 5: Recursive walk for $ref renaming, enum, required, description fixes
    $spec = $this->recursiveFixValues($spec, $renameMap);

    // Phase 6: Build global tags array from all operations
    $allTags = [];
    foreach ($spec['paths'] as $pathItem) {
      foreach ($pathItem as $op) {
        if (is_array($op) && isset($op['tags'])) {
          foreach ($op['tags'] as $tag) { $allTags[$tag] = true; }
        }
      }
    }
    ksort($allTags);
    $spec['tags'] = array_map(fn($name) => ['name' => $name], array_keys($allTags));

    // Phase 7: Remove unreferenced component schemas (iterative until stable)
    if (isset($spec['components']['schemas'])) {
      $changed = true;
      while ($changed) {
        $changed = false;
        $refs = [];
        $this->collectSchemaRefs($spec['paths'], $refs);
        $this->collectSchemaRefs($spec['components']['schemas'], $refs);
        foreach (array_keys($spec['components']['schemas']) as $name) {
          if (!isset($refs[$name])) {
            unset($spec['components']['schemas'][$name]);
            $changed = true;
          }
        }
      }
    }

    return $spec;
  }

  private function collectSchemaRefs(mixed $data, array &$refs): void {
    if (!is_array($data) && !is_object($data)) return;
    if (is_object($data)) $data = (array)$data;
    foreach ($data as $key => $value) {
      if ($key === '$ref' && is_string($value) && str_starts_with($value, '#/components/schemas/')) {
        $refs[substr($value, strlen('#/components/schemas/'))] = true;
      } elseif (is_array($value) || is_object($value)) {
        $this->collectSchemaRefs($value, $refs);
      }
    }
  }

  private function recursiveFixValues(array $data, array $renameMap): array {
    foreach ($data as $key => &$value) {
      // Fix: description as array -> string (skip schema objects named "description")
      if ($key === 'description' && is_array($value) && !isset($value['type'])) {
        $value = implode("\n", $value);
        continue;
      }

      // Fix: enum as string -> proper array
      if ($key === 'enum' && is_string($value)) {
        if (preg_match_all("/'([^']+)'/", $value, $matches)) {
          $value = $matches[1];
        }
        continue;
      }

      // Fix: properties must be a JSON object, not array
      if ($key === 'properties' && is_array($value)) {
        if (empty($value)) {
          // Empty array -> stdClass so json_encode outputs {} not []
          $value = new \stdClass();
          continue;
        }
        if (isset($value[0])) {
          // Indexed array -- try merging elements that have 'properties' sub-keys
          $canMerge = true;
          foreach ($value as $item) {
            if (!is_array($item) || !isset($item['properties'])) {
              $canMerge = false;
              break;
            }
          }
          if ($canMerge) {
            $merged = [];
            foreach ($value as $item) {
              $merged = array_merge($merged, $item['properties']);
            }
            $value = $merged;
          } else {
            // Non-mergeable indexed array -> force to object
            $value = (object)$value;
            continue;
          }
        }
      }

      if (is_array($value)) {
        $value = $this->recursiveFixValues($value, $renameMap);
      } else {
        // Fix: $ref renaming (update references to renamed schemas)
        if ($key === '$ref' && is_string($value) && str_starts_with($value, '#/components/schemas/')) {
          $schemaName = substr($value, strlen('#/components/schemas/'));
          if (isset($renameMap[$schemaName])) {
            $value = '#/components/schemas/' . $renameMap[$schemaName];
          }
        }
        // Fix: required as string "true" -> boolean true
        if ($key === 'required' && $value === "true") {
          $value = true;
        }
      }
    }
    unset($value);
    return $data;
  }
}
