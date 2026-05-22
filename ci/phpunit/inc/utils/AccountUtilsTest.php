<?php

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\Encryption;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\defines\DAccountAction;
use Hashtopolis\inc\utils\AccountUtils;
use Hashtopolis\inc\utils\UserUtils;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class AccountUtilsTest extends TestBase {
  
	public function testCheckOTPDisablesYubikeyWhenNoValidPrefixExists(): void {
		$user = $this->createUser('invalid_yubikey_user');
		$user->setYubikey(1);
		$user->setOtp1('short');
		$user->setOtp2('');
		$user->setOtp3('12345678901');
		$user->setOtp4('1234567890123');

		AccountUtils::checkOTP($user);

		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		$this->assertSame('0', $reloadedUser->getYubikey());
		$this->assertSame('short', $reloadedUser->getOtp1());
		$this->assertSame('', $reloadedUser->getOtp2());
		$this->assertSame('12345678901', $reloadedUser->getOtp3());
		$this->assertSame('1234567890123', $reloadedUser->getOtp4());
	}

	public function testCheckOTPKeepsYubikeyEnabledWhenOtp1HasAValidPrefix(): void {
		$this->assertCheckOTPKeepsYubikeyEnabledForValidSlot(1);
	}

	public function testCheckOTPKeepsYubikeyEnabledWhenOtp2HasAValidPrefix(): void {
		$this->assertCheckOTPKeepsYubikeyEnabledForValidSlot(2);
	}

	public function testCheckOTPKeepsYubikeyEnabledWhenOtp3HasAValidPrefix(): void {
		$this->assertCheckOTPKeepsYubikeyEnabledForValidSlot(3);
	}

	public function testCheckOTPKeepsYubikeyEnabledWhenOtp4HasAValidPrefix(): void {
		$this->assertCheckOTPKeepsYubikeyEnabledForValidSlot(4);
	}

	public function testSetOTPThrowsWhenEnablingWithoutAValidConfiguredKey(): void {
		$user = $this->createUser('setotp_invalid_enable_user');
		$user->setOtp1('short');
		$user->setOtp2('');
		$user->setOtp3('12345678901');
		$user->setOtp4('1234567890123');

		try {
			AccountUtils::setOTP(0, DAccountAction::YUBIKEY_ENABLE, $user, ['', '', '', '']);
			$this->fail('Expected setOTP to reject enabling Yubikey without a valid configured key.');
		}
		catch (HTException $exception) {
			$this->assertSame('Configure OTP KEY first!', $exception->getMessage());
		}

		$this->assertPersistedOtpState($user, '0', '', '', '', '');
	}

	public function testSetOTPDisableResetsYubikeyToZero(): void {
		$user = $this->createUser('setotp_disable_user');
		$user->setYubikey(1);
		$user->setOtp1('validyubikey');
		$user->setOtp2('backupyubico');
		$user->setOtp3('reservekey12');
		$user->setOtp4('lastresort12');

		AccountUtils::setOTP(-1, DAccountAction::YUBIKEY_DISABLE, $user, ['', '', '', '']);

		$this->assertPersistedOtpState($user, '0', 'validyubikey', 'backupyubico', 'reservekey12', 'lastresort12');
	}

	public function testSetOTPYubikeyActivationWithoutValidKeysDisabledAfterCheckOTP(): void {
		$user = $this->createUser('setotp_activate_without_valid_keys_user');
		$user->setYubikey(0);
		$user->setOtp1('short');
		$user->setOtp2('');
		$user->setOtp3('12345678901');
		$user->setOtp4('1234567890123');

		AccountUtils::setOTP(0, DAccountAction::SET_OTP1, $user, ['', '', '', '']);

		$this->assertPersistedOtpState($user, '0', 'short', '', '12345678901', '1234567890123');
	}

	public function testSetOTPStoresValidPrefixThenActivatesYubikey(): void {
		foreach ([1, 2, 3, 4] as $slot) {
			$this->assertSetOTPStoresValidPrefixThenActivatesYubikeyForSlot($slot);
		}
	}

	public function testSetEmailThrowsOnInvalidEmailFormat(): void {
		$user = $this->createUser('invalid_email_user');
		$this->expectException(HTException::class);
		AccountUtils::setEmail('invalid-email-address', $user);
	}

	public function testSetEmailUpdatesEmailOnValidAddress(): void {
		$user = $this->createUser('valid_email_user');
		$newEmail = 'updated_' . uniqid() . '@example.com';

		AccountUtils::setEmail($newEmail, $user);

		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		$this->assertSame($newEmail, $reloadedUser->getEmail());
	}

	public function testUpdateSessionLifetimeThrowsWhenBelowMinimum(): void {
		$user = $this->createUser('invalid_lifetime_user');
		$this->expectException(HTException::class);

		AccountUtils::updateSessionLifetime(59, $user);
	}

	public function testUpdateSessionLifetimeUpdatesPersistedValue(): void {
		$user = $this->createUser('valid_lifetime_user');
		$newLifetime = 60;

		AccountUtils::updateSessionLifetime($newLifetime, $user);

		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		$this->assertSame($newLifetime, $reloadedUser->getSessionLifetime());
	}

	public function testChangePasswordThrowsWhenOldPasswordIsWrong(): void {
		$user = $this->createUserWithPassword('wrong_old_password_user', 'oldpass');
		$this->expectException(HTException::class);
		$this->expectExceptionMessage('Your old password is wrong!');

		AccountUtils::changePassword('wrongpass', 'newpass', 'newpass', $user);
	}

	public function testChangePasswordThrowsWhenNewPasswordIsTooShort(): void {
		$user = $this->createUserWithPassword('short_new_password_user', 'oldpass');
		$this->expectException(HTException::class);
		$this->expectExceptionMessage('Your password is too short!');

		AccountUtils::changePassword('oldpass', 'abc', 'abc', $user);
	}

	public function testChangePasswordThrowsWhenNewPasswordsDoNotMatch(): void {
		$user = $this->createUserWithPassword('mismatch_password_user', 'oldpass');
		$this->expectException(HTException::class);
		$this->expectExceptionMessage('Your new passwords do not match!');

		AccountUtils::changePassword('oldpass', 'newpass', 'otherpass', $user);
	}

	public function testChangePasswordThrowsWhenNewPasswordMatchesOldPassword(): void {
		$user = $this->createUserWithPassword('same_password_user', 'oldpass');
		$this->expectException(HTException::class);
		$this->expectExceptionMessage('Your new password is the same as the old one!');

		AccountUtils::changePassword('oldpass', 'oldpass', 'oldpass', $user);
	}

	public function testChangePasswordUpdatesPersistedPasswordData(): void {
		$user = $this->createUserWithPassword('happy_password_user', 'oldpass');
		$oldSalt = $user->getPasswordSalt();
		$oldHash = $user->getPasswordHash();

		AccountUtils::changePassword('oldpass', 'newpass', 'newpass', $user);

		$reloadedUser = $this->reloadUser($user);

		$this->assertNotSame($oldSalt, $reloadedUser->getPasswordSalt());
		$this->assertNotSame($oldHash, $reloadedUser->getPasswordHash());
		$this->assertFalse(Encryption::passwordVerify('oldpass', $reloadedUser->getPasswordSalt(), $reloadedUser->getPasswordHash()));
		$this->assertTrue(Encryption::passwordVerify('newpass', $reloadedUser->getPasswordSalt(), $reloadedUser->getPasswordHash()));
		$this->assertSame(0, $reloadedUser->getIsComputedPassword());
	}

  private function assertCheckOTPKeepsYubikeyEnabledForValidSlot(int $validSlot): void {
		$user = $this->createUser('valid_yubikey_user_' . $validSlot);
		$user->setYubikey(1);

		$otpValues = [
			1 => '',
			2 => '',
			3 => '',
			4 => '',
		];
		$otpValues[$validSlot] = 'validyubikey';

		$user->setOtp1($otpValues[1]);
		$user->setOtp2($otpValues[2]);
		$user->setOtp3($otpValues[3]);
		$user->setOtp4($otpValues[4]);

		AccountUtils::checkOTP($user);

		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		$this->assertSame('1', $reloadedUser->getYubikey());
		$this->assertSame($otpValues[1], $reloadedUser->getOtp1());
		$this->assertSame($otpValues[2], $reloadedUser->getOtp2());
		$this->assertSame($otpValues[3], $reloadedUser->getOtp3());
		$this->assertSame($otpValues[4], $reloadedUser->getOtp4());
	}

	private function assertPersistedOtpState(User $user, string $expectedYubikey, string $expectedOtp1, string $expectedOtp2, string $expectedOtp3, string $expectedOtp4): void {
		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		$this->assertSame($expectedYubikey, $reloadedUser->getYubikey());
		$this->assertSame($expectedOtp1, $reloadedUser->getOtp1());
		$this->assertSame($expectedOtp2, $reloadedUser->getOtp2());
		$this->assertSame($expectedOtp3, $reloadedUser->getOtp3());
		$this->assertSame($expectedOtp4, $reloadedUser->getOtp4());
	}

	private function assertSetOTPStoresValidPrefixThenActivatesYubikeyForSlot(int $slot): void {
		$user = $this->createUser('setotp_happy_path_user_' . $slot);
		$fullOtp = 'ccccccdefghdefghdefghdefghdefghdefghdefghi';
		$actions = [
			1 => DAccountAction::SET_OTP1,
			2 => DAccountAction::SET_OTP2,
			3 => DAccountAction::SET_OTP3,
			4 => DAccountAction::SET_OTP4,
		];
		$expectedOtpValues = [
			1 => '',
			2 => '',
			3 => '',
			4 => '',
		];
		$expectedOtpValues[$slot] = 'ccccccdefghd';

		$otpArr = ['', '', '', ''];
		$otpArr[$slot - 1] = $fullOtp;

		AccountUtils::setOTP($slot, $actions[$slot], $user, $otpArr);
		$this->assertPersistedOtpState($user, '0', $expectedOtpValues[1], $expectedOtpValues[2], $expectedOtpValues[3], $expectedOtpValues[4]);

		AccountUtils::setOTP(0, DAccountAction::YUBIKEY_ENABLE, $user, ['', '', '', '']);
		$this->assertPersistedOtpState($user, '1', $expectedOtpValues[1], $expectedOtpValues[2], $expectedOtpValues[3], $expectedOtpValues[4]);
	}


  
  /*
    Local test helpers
  */
	private function createRightGroup(): RightGroup {
		$group = $this->createDatabaseObject(
			Factory::getRightGroupFactory(),
			new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
		);
		$this->assertTrue($group instanceof RightGroup);
		return $group;
	}

	private function createUser(string $prefix): User {
		$username = $prefix . '_' . uniqid();
		$user = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
		$this->registerDatabaseObject(Factory::getUserFactory(), $user);
		return $user;
	}

	private function createUserWithPassword(string $prefix, string $password): User {
		$user = $this->createUser($prefix);
		UserUtils::setPassword($user->getId(), $password, $this->adminUser);
		return $this->reloadUser($user);
	}

	private function reloadUser(User $user): User {
		$reloadedUser = Factory::getUserFactory()->filter([
			Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
		], true);

		$this->assertInstanceOf(User::class, $reloadedUser);
		return $reloadedUser;
	}

	

}