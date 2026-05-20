<?php

namespace Hashtopolis\inc\apiv2\common;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\InternalError;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
trait HasGet {
  abstract protected function validatePermissions(string $permissions, array $required_perms, string $method, array $permsExpandMatching = []): bool|array;

  /**
   * Overridable function to aggregate data in the object. Used for Tasks and Agents.
   *
   * @param object $object The object to aggregate data from.
   * @param array &$includedData Array passed by reference; implementations can add related resources
   *   for inclusion in the response by appending to this array.
   * @return array Aggregated data as key-value pairs.
   *
   * Implementations should use $includedData to collect related resources that should be included
   * in the API response, such as related entities or additional data.
   */
  public function aggregateData(object $object, array &$includedData = [], ?array $aggregateFieldsets = null): array {
    return [];
  }

  /**
   * Return supported aggregate fieldsets/options for this endpoint.
   *
   * Format:
   * [
   *   'resourceKey' => ['option1', 'option2']
   * ]
   */
  public function getAggregateFieldsets(): array {
    return [];
  }
  
  /**
   * Check for valid expand parameters.
   * @throws HttpError
   * @throws InternalError
   */
  //TODO: nice to have would be to be able to include objects that are further away in the relationship
  //ex. from Hash include=hashlist.task to include all tasks from a hash (section 8.3 JSON API) 
  protected function makeExpandables(Request $request, array $validExpandables): array {
    $data = $request->getParsedBody();
    $queryExpands = (array_key_exists('include', $request->getQueryParams())) ? preg_split("/[,\ ]+/", $request->getQueryParams()['include']) : [];
    
    foreach ($queryExpands as $expand) {
      if (!in_array($expand, $validExpandables)) {
        throw new HttpError("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($validExpandables)) . ")");
      }
    }
    
    /* Validate expand parameters for required permissions */
    $required_perms = [];
    $permsExpandMatching = [];
    foreach ($queryExpands as $expand) {
      $expandedPerms = self::getExpandPermissions($expand);
      foreach ($expandedPerms as $expandedPerm) {
        if (!isset($permsExpandMatching[$expandedPerm])) {
          $permsExpandMatching[$expandedPerm] = [$expand];
        }
        else {
          $permsExpandMatching[$expandedPerm][] = $expand;
        }
      }
      array_push($required_perms, ...$expandedPerms);
    }
    $permissionResponse = $this->validatePermissions($request->getAttribute("scope"), $required_perms, $request->getMethod(), $permsExpandMatching);
    $expands_to_remove = [];
    
    // remove expands with missing permissions
    foreach ($this->missing_permissions as $missing_permission) {
      $expands_to_remove = array_merge($expands_to_remove, $permsExpandMatching[$missing_permission]);
    }
    $queryExpands = array_diff($queryExpands, $expands_to_remove);
    
    // if ($permissionResponse === FALSE) {
    //   throw new HttpError('Permissions missing on expand parameter objects! || ' . join('||', $this->permissionErrors));
    // }
    return $queryExpands;
  }

  /**
   * Retrieve permissions based on expand section
   * @throws InternalError
   */
  protected static function getExpandPermissions(string $expand): array {
    $expand_to_perm_mapping = array(
      'assignedAgents' => [Agent::PERM_READ],
      'assignments' => [Assignment::PERM_READ],
      'agent' => [Agent::PERM_READ],
      'agents' => [AccessGroup::PERM_READ],
      'agentErrors' => [AgentError::PERM_READ],
      'agentStats' => [AgentStat::PERM_READ],
      'accessGroups' => [AccessGroup::PERM_READ],
      'accessGroup' => [AccessGroup::PERM_READ],
      'chunk' => [Chunk::PERM_READ],
      'chunks' => [Chunk::PERM_READ],
      'configSection' => [ConfigSection::PERM_READ],
      'crackerBinary' => [CrackerBinary::PERM_READ],
      'crackerBinaryType' => [CrackerBinaryType::PERM_READ],
      'crackerVersions' => [CrackerBinary::PERM_READ],
      'hashes' => [Hash::PERM_READ],
      'hashlist' => [Hashlist::PERM_READ],
      'hashlists' => [Hashlist::PERM_READ],
      'hashType' => [HashType::PERM_READ],
      'healthCheck' => [HealthCheck::PERM_READ],
      'healthCheckAgents' => [HealthCheckAgent::PERM_READ],
      'globalPermissionGroup' => [RightGroup::PERM_READ],
      'task' => [Task::PERM_READ],
      'tasks' => [Task::PERM_READ],
      'speeds' => [Speed::PERM_READ],
      'pretaskFiles' => [FilePretask::PERM_READ, File::PERM_READ],
      'files' => [FileTask::PERM_READ, File::PERM_READ],
      'pretasks' => [Supertask::PERM_READ, Pretask::PERM_READ],
      'user' => [User::PERM_READ],
      'users' => [User::PERM_READ],
      'userMembers' => [User::PERM_READ],
      'agentMembers' => [Agent::PERM_READ],
    );
    
    if (array_key_exists($expand, $expand_to_perm_mapping) === False) {
      throw new InternalError("Internal error: Expand type '$expand' has no permission mapping implemented in getExpandPermissions()!");
    }
    return $expand_to_perm_mapping[$expand];
  }
}