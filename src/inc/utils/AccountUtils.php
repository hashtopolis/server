<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\inc\Encryption;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DAccountAction;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class AccountUtils {
  /**
   * @param User $user
   */
  public static function checkOTP($user) {
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
    Factory::getUserFactory()->update($user);
  }
  
  /**
   * @param $num
   * @param $action
   * @param $user User
   * @param $otpArr
   * @throws HTException
   */
  public static function setOTP($num, $action, $user, $otpArr) {
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
    Factory::getUserFactory()->update($user);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed OTP!");
  }
  
  /**
   * @param string $email
   * @param User $user
   * @throws HTException
   */
  public static function setEmail($email, $user) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new HTException("Invalid email address!");
    }
    
    Factory::getUserFactory()->set($user, User::EMAIL, $email);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed email!");
  }
  
  /**
   * @param int $lifetime
   * @param User $user
   * @throws HTException
   */
  public static function updateSessionLifetime($lifetime, $user) {
    $lifetime = intval($lifetime);
    if ($lifetime < 60 || $lifetime > SConfig::getInstance()->getVal(DConfig::MAX_SESSION_LENGTH) * 3600) {
      throw new HTException("Lifetime must be larger than 1 minute and smaller than " . SConfig::getInstance()->getVal(DConfig::MAX_SESSION_LENGTH) . " hours!");
    }
    
    Factory::getUserFactory()->set($user, User::SESSION_LIFETIME, $lifetime);
  }
  
  /**
   * @param string $oldPassword
   * @param string $newPassword
   * @param string $repeatedPassword
   * @param User $user
   * @throws HTException
   */
  public static function changePassword($oldPassword, $newPassword, $repeatedPassword, $user) {
    if (!Encryption::passwordVerify($oldPassword, $user->getPasswordSalt(), $user->getPasswordHash())) {
      throw new HTException("Your old password is wrong!");
    }
    else if (strlen($newPassword) < 4) {
      throw new HTException("Your password is too short!");
    }
    else if ($newPassword != $repeatedPassword) {
      throw new HTException("Your new passwords do not match!");
    }
    else if ($newPassword == $oldPassword) {
      throw new HTException("Your new password is the same as the old one!");
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPassword, $newSalt);
    
    Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, USer::IS_COMPUTED_PASSWORD => 0]);
    
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed password!");
  }
}