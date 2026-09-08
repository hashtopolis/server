<?php

namespace Hashtopolis\inc\jobs;

class BackgroundJobResult {
  private int $exitCode;
  private ?string $message;

  public function __construct(int $exitCode, ?string $message = null) {
    $this->exitCode = $exitCode;
    $this->message = $message;
  }

  public function getExitCode(): int {
    return $this->exitCode;
  }

  public function getMessage(): ?string {
    return $this->message;
  }
}
