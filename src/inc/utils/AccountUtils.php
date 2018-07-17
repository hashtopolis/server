<?php
use DBA\User;

class AccountUtils {
  /**
   * @param User $user
   */
  public static function checkOTP($user) {
    global $FACTORIES;

    $isValid = false;

    if (strlen($user->getOtp1()) == 12) {
      $isValid = true;
    }
    else if (strlen($user->getOtp2()) == 12) {
      $isValid = true;
    }
    else if (strlen($user->getOtp3()) == 12) {
      $isValid = true;
    }
    else if (strlen($user->getOtp4()) == 12) {
      $isValid = true;
    }
    if (!$isValid) {
      $user->setYubikey(0);
    }
    $FACTORIES::getUserFactory()->update($user);
  }

  public static function setOTP($num, $action, $user, $otpArr) {
    global $FACTORIES;

    if ($action == DAccountAction::YUBIKEY_ENABLE) {
      $isValid = false;

      if (strlen($user->getOtp1()) == 12) {
        $isValid = true;
      }
      else if (strlen($user->getOtp2()) == 12) {
        $isValid = true;
      }
      else if (strlen($user->getOtp3()) == 12) {
        $isValid = true;
      }
      else if (strlen($user->getOtp4()) == 12) {
        $isValid = true;
      }

      if (!$isValid) {
        throw new HTException("Configure OTP KEY first!");
      }
    }

    switch ($num) {
      case -1:
        $user->setYubikey(0);
        break;
      case 0:
        $user->setYubikey(1);
        break;
      case 1:
        $otp = $otpArr[0];
        $user->setOtp1(substr($otp, 0, 12));
        break;
      case 2:
        $otp = $otpArr[1];
        $user->setOtp2(substr($otp, 0, 12));
        break;
      case 3:
        $otp = $otpArr[2];
        $user->setOtp3(substr($otp, 0, 12));
        break;
      case 4:
        $otp = $otpArr[3];
        $user->setOtp4(substr($otp, 0, 12));
        break;
      default:
        return;
    }

    AccountUtils::checkOTP($user);
    $FACTORIES::getUserFactory()->update($user);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed OTP!");
  }

  /**
   * @param string $email
   * @param User $user
   * @throws HTException
   */
  public static function setEmail($email, $user) {
    global $FACTORIES;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new HTException("Invalid email address!");
    }

    $user->setEmail($email);
    $FACTORIES::getUserFactory()->update($user);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed email!");
  }

  /**
   * @param int $lifetime
   * @param User $user
   * @throws HTException
   */
  public static function updateSessionLifetime($lifetime, $user) {
    global $FACTORIES;

    $lifetime = intval($lifetime);
    if ($lifetime < 60 || $lifetime > 48 * 3600) { // TODO: make maximum configurable
      throw new HTException("Lifetime must be larger than 1 minute and smaller than 2 days!");
    }

    $user->setSessionLifetime($lifetime);
    $FACTORIES::getUserFactory()->update($user);
  }

  /**
   * @param string $oldPassword
   * @param string $newPassword
   * @param string $repeatedPassword
   * @param User $user
   * @throws HTException
   */
  public static function changePassword($oldPassword, $newPassword, $repeatedPassword, $user) {
    global $FACTORIES;

    if (!Encryption::passwordVerify($oldPassword, $user->getPasswordSalt(), $user->getPasswordHash())) {
      throw new HTException("Your old password is wrong!");
    }
    else if (strlen($newPassword) < 4) {
      throw new HTException("Your password is too short!");
    }
    else if ($newPassword != $repeatedPassword) {
      throw new HTException("Your new passwords do not match!");
    }

    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPassword, $newSalt);
    $user->setPasswordHash($newHash);
    $user->setPasswordSalt($newSalt);
    $user->setIsComputedPassword(0);
    $FACTORIES::getUserFactory()->update($user);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed password!");
  }
}