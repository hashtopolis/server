<?php

namespace Hashtopolis\inc {
	function is_file($path) {
		if (isset($GLOBALS['user_utils_is_file_mock']) && is_callable($GLOBALS['user_utils_is_file_mock'])) {
			return $GLOBALS['user_utils_is_file_mock']($path);
		}
		return \is_file($path);
	}

	function mail($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) {
		if (isset($GLOBALS['user_utils_mail_mock']) && is_callable($GLOBALS['user_utils_mail_mock'])) {
			return $GLOBALS['user_utils_mail_mock']($to, $subject, $message, $additionalHeaders, $additionalParams);
		}
		if ($additionalParams === null) {
			return \mail($to, $subject, $message, $additionalHeaders ?? '');
		}
		return \mail($to, $subject, $message, $additionalHeaders ?? '', $additionalParams);
	}

	function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
		if (isset($GLOBALS['user_utils_util_error_log_mock']) && is_callable($GLOBALS['user_utils_util_error_log_mock'])) {
			return $GLOBALS['user_utils_util_error_log_mock']($message, $messageType, $destination, $additionalHeaders);
		}
		if ($destination === null) {
			return \error_log($message, $messageType);
		}
		if ($additionalHeaders === null) {
			return \error_log($message, $messageType, $destination);
		}
		return \error_log($message, $messageType, $destination, $additionalHeaders);
	}
}

namespace Hashtopolis\inc\utils {
	function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
		if (isset($GLOBALS['user_utils_error_log_mock']) && is_callable($GLOBALS['user_utils_error_log_mock'])) {
			return $GLOBALS['user_utils_error_log_mock']($message, $messageType, $destination, $additionalHeaders);
		}
		if ($destination === null) {
			return \error_log($message, $messageType);
		}
		if ($additionalHeaders === null) {
			return \error_log($message, $messageType, $destination);
		}
		return \error_log($message, $messageType, $destination, $additionalHeaders);
	}
}

namespace Hashtopolis\inc\templating {
	class Template {
		private string $name;

		public function __construct(string $name) {
			$this->name = $name;
		}

		public function render($data): string {
			return $this->name . ':' . json_encode($data);
		}
	}
}

namespace Tests\Inc\Utils {

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\utils\UserUtils;
use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class UserUtilsTest extends TestCase {
	/** @var string[] */
	private array $createdUsernames = [];

	/** @var int[] */
	private array $createdRightGroupIds = [];

	protected function setUp(): void {
		parent::setUp();

		$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
		unset(
			$GLOBALS['user_utils_is_file_mock'],
			$GLOBALS['user_utils_mail_mock'],
			$GLOBALS['user_utils_util_error_log_mock'],
			$GLOBALS['user_utils_error_log_mock']
		);
	}

	protected function tearDown(): void {
		foreach ($this->createdUsernames as $username) {
			$qF = new QueryFilter(User::USERNAME, $username, '=');
			$users = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
			foreach ($users as $user) {
				$memberFilter = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), '=');
				Factory::getAccessGroupUserFactory()->massDeletion([Factory::FILTER => $memberFilter]);
				Factory::getUserFactory()->delete($user);
			}
		}

		foreach ($this->createdRightGroupIds as $rightGroupId) {
			$group = Factory::getRightGroupFactory()->get($rightGroupId);
			if ($group !== null) {
				Factory::getRightGroupFactory()->delete($group);
			}
		}

		unset(
			$GLOBALS['user_utils_is_file_mock'],
			$GLOBALS['user_utils_mail_mock'],
			$GLOBALS['user_utils_util_error_log_mock'],
			$GLOBALS['user_utils_error_log_mock']
		);

		parent::tearDown();
	}

	public function testCreateUserDoesNotCallSendMailWhenMailIsNotConfigured(): void {
		$mailCallCount = 0;
		$utilErrorLogMessages = [];
		$username = $this->uniqueUsername('mail_disabled');

		$GLOBALS['user_utils_is_file_mock'] = static function ($path): bool {
			return false;
		};
		$GLOBALS['user_utils_mail_mock'] = static function () use (&$mailCallCount): bool {
			$mailCallCount++;
			return true;
		};
		$GLOBALS['user_utils_util_error_log_mock'] = static function ($message) use (&$utilErrorLogMessages): bool {
			$utilErrorLogMessages[] = $message;
			return true;
		};

		$createdUser = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
		$this->createdUsernames[] = $username;

		$this->assertSame($username, $createdUser->getUsername());
		$this->assertSame(0, $mailCallCount);
		$this->assertSame([], $utilErrorLogMessages);
	}

	public function testCreateUserCallsSendMailWhenMailIsConfigured(): void {
		$mailCalls = [];
		$userUtilsErrorLogMessages = [];
		$username = $this->uniqueUsername('mail_enabled');

		$GLOBALS['user_utils_is_file_mock'] = static function ($path): bool {
			return true;
		};
		$GLOBALS['user_utils_mail_mock'] = static function ($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) use (&$mailCalls): bool {
			$mailCalls[] = [$to, $subject, $message, $additionalHeaders, $additionalParams];
			return true;
		};
		$GLOBALS['user_utils_error_log_mock'] = static function ($message) use (&$userUtilsErrorLogMessages): bool {
			$userUtilsErrorLogMessages[] = $message;
			return true;
		};

		UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
		$this->createdUsernames[] = $username;

		$this->assertCount(1, $mailCalls);
		$this->assertSame($username . '@example.com', $mailCalls[0][0]);
		$this->assertSame('Account at ' . APP_NAME, $mailCalls[0][1]);
		$this->assertSame([], $userUtilsErrorLogMessages);
	}

	public function testCreateUserLogsWhenConfiguredSendMailFails(): void {
		$mailCallCount = 0;
		$userUtilsErrorLogMessages = [];
		$username = $this->uniqueUsername('mail_failure');

		$GLOBALS['user_utils_is_file_mock'] = static function ($path): bool {
			return true;
		};
		$GLOBALS['user_utils_mail_mock'] = static function () use (&$mailCallCount): bool {
			$mailCallCount++;
			return false;
		};
		$GLOBALS['user_utils_error_log_mock'] = static function ($message) use (&$userUtilsErrorLogMessages): bool {
			$userUtilsErrorLogMessages[] = $message;
			return true;
		};

		UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
		$this->createdUsernames[] = $username;

		$this->assertSame(1, $mailCallCount);
		$this->assertSame(['Unable to send mail to user with subject: Account at ' . APP_NAME], $userUtilsErrorLogMessages);
	}

	private function createAdminUser(): User {
		return new User(1, 'admin', 'admin@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, 1, '', '', '', '', '');
	}

	private function createRightGroup(): RightGroup {
		$group = Factory::getRightGroupFactory()->save(new RightGroup(null, 'phpunit-' . uniqid('', true), '[]'));
		$this->createdRightGroupIds[] = $group->getId();
		return $group;
	}

	private function uniqueUsername(string $prefix): string {
		return $prefix . '_' . uniqid();
	}
}
}
