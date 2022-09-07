<?php

namespace APIv2;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Luracast\Restler\Compose;

class SwaggerPatchCompose extends Compose {

  public function response($result) {
    if (isset($result->swagger)) {
      // if we detect the 'swagger' property, assume we are requesting swagger.json and execute necessary patches
      foreach ($result->definitions as $dto) {
        $this->patchDto($dto);
      }
    }
    return $result;
  }

  // current patches:
  //   - detect boolean properties and default their values to 'false', in case no default is set
  //   - the swagger UI expects default values to be set using a 'default' property, 
  //     but Restler sets these using 'defaultValue', patch this as well
  //   - coerce default value to expected type, as default seems to always be string;
  //     for now, only for 'int' and 'boolean' specifically
  private function patchDto($dto) {
    foreach ($dto->properties as $name => $attributes) {
      if (isset($attributes->defaultValue)) {
        $attributes->default = match ($attributes->type) {
          'boolean' => boolval($attributes->defaultValue),
          'integer' => intval($attributes->defaultValue),
          default   => $attributes->defaultValue
        };
      // if no default value is set, set to sane one
      } else if ($attributes->type == 'boolean') {
        $attributes->default = match ($attributes->type) {
          'boolean' => false,
          default   => $attributes->defaultValue
        };
      }
    }
  }
}