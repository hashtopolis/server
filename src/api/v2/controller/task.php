<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../dto/mapping.php';
require_once __DIR__ . '/../dto/taskDTO.php';
require_once __DIR__ . '/../util/statusCode.php';

use Luracast\Restler\RestException;

use AccessUtils,
    TaskUtils,
    HTException;

use DBA\Factory;

class TaskController extends ApiController {

  /**
   * Gets all normal tasks which are accessible.
   * 
   * Nested objects can be expanded by passing them as a query parameter,
   * for example `/tasks/normal?expand=hashlist`.
   * 
   * @url GET normal
   * @access protected
   * 
   * @param $expand {@from query} {@type string} {@pattern /^\w+(,\w+)*$/}
   *        comma separated names of the nested items to include in the response
   * 
   * @return array {@type GetNormalTask} 
   */
  public function getNormalTasks(string $expand='none') {
    $taskWrappers = array_filter(
      TaskUtils::getTaskWrappersForUser($this->_user()),
      fn($taskWrapper) => $taskWrapper->getTaskType() == 0
    );
    $tasks = array_map(fn($taskWrapper) => Mapping::getNormalTask($taskWrapper), array_values($taskWrappers));
    return $this->_expand($tasks, $expand);
  }

  /**
   * Gets the normal task with given id, if accessible.
   * 
   * Nested objects can be expanded by passing them as a query parameter,
   * for example `/tasks/normal/1?expand=hashlist`.
   * 
   * @url GET normal/{id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the normal task to get
   * @param $expand {@from query} {@type string} {@pattern /^\w+(,\w+)*$/}
   *        comma separated names of the nested items to include in the response
   * 
   * @return GetNormalTask
   * @throws RestException
   */
  public function getNormalTask(int $id, string $expand='none') {
    $task = Mapping::getNormalTask($this->_findSingleNormalTaskWrapper($id));
    return $this->_expand($task, $expand);
  }

  /**
   * Gets all super tasks which are accessible.
   * 
   * Nested objects can be expanded by passing them as a query parameter,
   * for example `/tasks/super?expand=hashlist,accessGroup`.
   * 
   * @url GET super
   * @access protected
   * 
   * @param $expand {@from query} {@type string} {@pattern /^\w+(,\w+)*$/}
   *        comma separated names of the nested items to include in the response
   * 
   * @return array {@type GetSuperTask} 
   */
  public function getSuperTasks(string $expand='none') {
    $taskWrappers = array_filter(
      TaskUtils::getTaskWrappersForUser($this->_user()),
      fn($taskWrapper) => $taskWrapper->getTaskType() == 1
    );
    $tasks = array_map(fn($taskWrapper) => Mapping::getSuperTask($taskWrapper), array_values($taskWrappers));
    return $this->_expand($tasks, $expand);
  }

  /**
   * Gets the super task with given id, if accessible.
   * 
   * Nested objects can be expanded by passing them as a query parameter,
   * for example `/tasks/super/1?expand=hashlist,accessGroup`.
   * 
   * @url GET super/{id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the supert task to get
   * @param $expand {@from query} {@type string} {@pattern /^\w+(,\w+)*$/}
   *        comma separated names of the nested items to include in the response
   * 
   * @return GetSuperTask
   * @throws RestException
   */
  public function getSuperTask(int $id, string $expand='none') {
    $task = Mapping::getSuperTask($this->_findSingleSuperTaskWrapper($id));
    return $this->_expand($task, $expand);
  }

  // TODO: or return the new object
  /**
   * Updates an existing normal task, if accessible.
   * 
   * @url PATCH normal/{id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the normal task to patch
   * @param UpdateNormalTask {@from body} 
   *        the properties to update
   * 
   * @status 204
   * @throws RestException
   */
  public function updateNormalTask(int $id, UpdateNormalTask $update) {
    $task = $this->_findSingleNormalTaskWrapper($id);
    try {
      // TODO: create single 'atomic' update function in TaskUtils?
      if ($update->priority != null) {
        TaskUtils::updatePriority($task->getId(), $update->priority, $this->_user());
      }
      if ($update->maxAgents != null) {
        TaskUtils::updateMaxAgents($task->getId(), $update->maxAgents, $this->_user());
      }
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  /**
   * Removes the normal task with given id, if accessible.
   * 
   * @url DELETE normal/{id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the normal task to delete
   *
   * @status 204
   * @throws RestException
   */
  public function deleteNormalTask(int $id) {
    // unused, called only for access checks in _findSingleNormalTaskWrapper
    $unused = $this->_findSingleNormalTaskWrapper($id);
    try {
      TaskUtils::delete($id, $this->_user(), true);
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  /**
   * Removes the super task with given id, if accessible.
   * 
   * @url DELETE super/{id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the super task to delete
   *
   * @status 204
   * @throws RestException
   */
  public function deleteSuperTask($id) {
    $result = $this->_findSingleSuperTaskWrapper($id);
    try {
      TaskUtils::deleteSuperTask($result->getId(), $this->_user());
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  private function _findSingleNormalTaskWrapper($id) {
    $task = Factory::getTaskFactory()->get($id);
    if ($task == null) {
      throw new RestException(StatusCode::NOT_FOUND, 'normal task not found for id: ' . $id);
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $this->_user())) {
      throw new RestException(StatusCode::BAD_REQUEST, 'no access to normal task with id: ' . $id);
    }
    return $taskWrapper;
  }

  private function _findSingleSuperTaskWrapper($id) {
    $taskWrapper = Factory::getTaskWrapperFactory()->get($id);
    if ($taskWrapper == null || $taskWrapper->getTaskType() != 1) {
      throw new RestException(StatusCode::NOT_FOUND, 'super task not found for id: ' . $id);
    }
    if (!AccessUtils::userCanAccessTask($taskWrapper, $this->_user())) {
      throw new RestException(StatusCode::BAD_REQUEST, 'no access to super task with id: ' . $id);
    }
    return $taskWrapper;
  }

  private function _expand($input, $expand) {
    if ($expand == 'none') {
      return $input;
    }

    // TODO: rework this some time later (and for now also let it blow up if handler is invalid)
    $expansions = array(
      'hashlist'    => fn($task) => $this->_expandHashlist($task),
      'accessGroup' => fn($task) => $this->_expandAccessGroup($task)
    );

    $toExpand = explode(',', $expand);
    foreach ($expansions as $name => $handler) {
      if (in_array($name, $toExpand)) {
        if (is_array($input)) {
          $input = array_map(fn($task) => $handler($task), $input);
        } else {
          $input = $handler($input);
        }
      }
    }
    return $input;
  }

  private function _expandHashlist($task) {
    $api = new HashlistController();
    $task->hashlist = $api->getHashlist($task->hashlistId);
    return $task;
  }

  private function _expandAccessGroup($task) {
    $api = new AccessController();
    $task->accessGroup = $api->getAccessGroup($task->accessGroupId);
    return $task;
  }
}
