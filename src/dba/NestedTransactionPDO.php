<?php

namespace Hashtopolis\dba;

use PDO;

/**
 * Database connection that tolerates nested transactions.
 *
 * Both the API layer and the *Utils classes open transactions of their own
 * (e.g. AgentUtils::delete), so a request that has to group several of those
 * calls into a single unit of work runs into PDO's "There is already an active
 * transaction". The JSON:API atomic operations endpoint
 * (AbstractModelAPI::atomicOperations) needs exactly that: the extension
 * requires all operations of a request to be applied atomically.
 *
 * Only the outermost begin/commit/rollBack talks to the driver, a nested one is
 * mapped onto a savepoint, which both MySQL and PostgreSQL support. At nesting
 * depth zero every method behaves exactly like plain PDO, so the existing
 * single-transaction call sites are unaffected.
 */
class NestedTransactionPDO extends PDO {
  /** Number of transactions currently open, the outermost one included. */
  private int $transactionDepth = 0;

  public function beginTransaction(): bool {
    if ($this->transactionDepth === 0) {
      $result = parent::beginTransaction();
    }
    else {
      $result = $this->exec("SAVEPOINT " . $this->savepointName($this->transactionDepth)) !== false;
    }

    if ($result) {
      $this->transactionDepth++;
    }
    return $result;
  }

  /**
   * Committing a nested transaction only releases its savepoint: whether the
   * changes are persisted is decided by the outermost commit.
   */
  public function commit(): bool {
    if ($this->transactionDepth > 1) {
      $result = $this->exec("RELEASE SAVEPOINT " . $this->savepointName($this->transactionDepth - 1)) !== false;
      if ($result) {
        $this->transactionDepth--;
      }
      return $result;
    }

    $result = parent::commit();
    $this->transactionDepth = 0;
    return $result;
  }

  /**
   * Rolling back a nested transaction undoes it up to its savepoint and leaves
   * the enclosing transaction open.
   */
  public function rollBack(): bool {
    if ($this->transactionDepth > 1) {
      $result = $this->exec("ROLLBACK TO SAVEPOINT " . $this->savepointName($this->transactionDepth - 1)) !== false;
      if ($result) {
        $this->transactionDepth--;
      }
      return $result;
    }

    $result = parent::rollBack();
    $this->transactionDepth = 0;
    return $result;
  }

  /** Nesting depth of the transaction currently open, 0 when there is none. */
  public function getTransactionDepth(): int {
    return $this->transactionDepth;
  }

  private function savepointName(int $depth): string {
    return "hashtopolis_savepoint_" . $depth;
  }
}
