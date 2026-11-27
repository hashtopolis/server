<?php

namespace Hashtopolis\inc\apiv2\auth;

use Exception;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\utils\AccessUtils;

/**
 * Who is making an APIv2 request, as established by JWTBeforeHandler.
 *
 * For a login token, the user comes from the token's claims and is never read from the database, so
 * the User object is partial: only the id, username, rightGroupId and isValid fields are set. Code that
 * needs any other column (email, password hash, session lifetime, ...) must load the user itself.
 *
 * Access-group memberships are deliberately not part of the token: a user added to a group must see
 * its objects straight away, not once the token has been renewed. They are read on first use and kept
 * for the rest of the request.
 */
final class RequestIdentity {
  /** @var int[]|null ids of the user's access groups, loaded on first use */
  private ?array $accessGroupIds = null;

  /**
   * @param User $user the user the token was issued to (partial, see class doc)
   * @param string $scope the permissions granted by the token: 'ALL', or a JSON-encoded permission dict
   * @param string $aud the token audience, see USER_AUD and ApiTokenAPI::API_AUD
   * @param bool $isAdmin whether the user's right group holds the 'ALL' permissions. Not the same as
   *   $scope being 'ALL': an API token of an administrator carries a scope dict of its own.
   */
  public function __construct(
    public readonly User $user,
    public readonly string $scope,
    public readonly string $aud,
    public readonly bool $isAdmin,
  ) {
  }

  /**
   * Ids of the access groups the user is a member of
   *
   * @return int[]
   * @throws Exception
   */
  public function accessGroupIds(): array {
    if ($this->accessGroupIds === null) {
      $this->accessGroupIds = AccessUtils::getAccessGroupIdsOfUser($this->user->getId());
    }
    return $this->accessGroupIds;
  }
}
