<?php
/*
 * $Id: $
 */

class Bill extends AbstractModel {
	private $modelName = "Bill";
	
	// Modelvariables
	private $billId;
	private $userId;
	private $isPaid;
	private $userIsNotified;
	private $notes;
	
	public function getName() {
		return $this->modelName;
	}
	
	function __construct($billId, $userId, $isPaid, $userIsNotified, $notes) {
		$this->billId = $billId;
		$this->userId = $userId;
		$this->isPaid = $isPaid;
		$this->userIsNotified = $userIsNotified;
		$this->notes = $notes;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['billId'] = $this->billId;
		$dict['userId'] = $this->userId;
		$dict['isPaid'] = $this->isPaid;
		$dict['userIsNotified'] = $this->userIsNotified;
		$dict['notes'] = $this->notes;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "billId";
	}
	
	function getPrimaryKeyValue() {
		return $this->billId;
	}
	
	function getId() {
		return $this->billId;
	}
	
	function setId($id) {
		$this->billId = $id;
	}

	function getUserId(){
		return $this->userId;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}

	function getIsPaid(){
		return $this->isPaid;
	}

	function setIsPaid($isPaid){
		$this->isPaid = $isPaid;
	}

	function getUserIsNotified(){
		return $this->userIsNotified;
	}

	function setUserIsNotified($userIsNotified){
		$this->userIsNotified = $userIsNotified;
	}

	function getNotes(){
		return $this->notes;
	}

	function setNotes($notes){
		$this->notes = $notes;
	}
}
