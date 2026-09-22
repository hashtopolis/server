<?php

namespace Hashtopolis\inc\defines;

/**
 * The `type` claim of a JWT, which says what the token may be used for.
 *
 * A token is only ever meant for one purpose, but every token this deployment issues is signed with
 * the same key, so nothing but a claim keeps them apart. Naming the purpose inside the token lets a
 * credential minted for one endpoint be refused at another, rather than being accepted because it
 * happens to carry a valid signature.
 */
class DTokenType {
  /** Authorises resource requests. Carried by both the login token and API tokens. */
  const ACCESS = "access";

  /** Authorises renewing a session, and nothing else. */
  const REFRESH = "refresh";
}
