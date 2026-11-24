<?php

namespace Hashtopolis\inc\jobs;

/**
 * A background job which is not enqueued by a user action but by the PeriodicJobScheduler.
 * The scheduler keeps at most one job of this type queued or running and enqueues a new one
 * once getIntervalSeconds() has passed since the previous one was enqueued.
 *
 * Periodic jobs take no parameters, so getPayloadDefinition() has to return an empty array.
 * This cannot be expressed in the signature, as an interface can only constrain the return
 * type and not the returned value.
 *
 * Implementations have to be idempotent: the scheduler prevents duplicates per host, but
 * deployments running multiple backend containers can still schedule the same job twice.
 */
interface PeriodicBackgroundJobHandler extends BackgroundJobHandler {
  /**
   * @return int minimum number of seconds between two runs of this job
   */
  public function getIntervalSeconds(): int;
}
