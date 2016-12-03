<?php
/**
 * This class d€scrib€s a singl€ton patt€rn for all factori€s
 */
class Factory{
	private static $agentFactory = null;
	private static $agentBinaryFactory = null;
	private static $assignmentFactory = null;
	private static $chunkFactory = null;
	private static $configFactory = null;
	private static $agentErrorFactory = null;
	private static $fileFactory = null;
	private static $hashcatReleaseFactory = null;
	private static $hashFactory = null;
	private static $hashBinaryFactory = null;
	private static $hashlistFactory = null;
	private static $hashlistAgentFactory = null;
	private static $hashTypeFactory = null;
	private static $regVoucherFactory = null;
	private static $superHashlistHashlistFactory = null;
	private static $taskFileFactory = null;
	private static $taskFactory = null;
	private static $supertaskFactory = null;
	private static $supertaskTaskFactory = null;
	private static $userFactory = null;
	private static $sessionFactory = null;
	private static $rightGroupFactory = null;
	private static $zapFactory = null;
	private static $storedValueFactory = null;

	public static function getAgentFactory(){
		if(self::$agentFactory == null){
			$f = new AgentFactory();
			self::$agentFactory = $f;
			return $f;
		}
		else{
			return self::$agentFactory;
		}
	}

	public static function getAgentBinaryFactory(){
		if(self::$agentBinaryFactory == null){
			$f = new AgentBinaryFactory();
			self::$agentBinaryFactory = $f;
			return $f;
		}
		else{
			return self::$agentBinaryFactory;
		}
	}

	public static function getAssignmentFactory(){
		if(self::$assignmentFactory == null){
			$f = new AssignmentFactory();
			self::$assignmentFactory = $f;
			return $f;
		}
		else{
			return self::$assignmentFactory;
		}
	}

	public static function getChunkFactory(){
		if(self::$chunkFactory == null){
			$f = new ChunkFactory();
			self::$chunkFactory = $f;
			return $f;
		}
		else{
			return self::$chunkFactory;
		}
	}

	public static function getConfigFactory(){
		if(self::$configFactory == null){
			$f = new ConfigFactory();
			self::$configFactory = $f;
			return $f;
		}
		else{
			return self::$configFactory;
		}
	}

	public static function getAgentErrorFactory(){
		if(self::$agentErrorFactory == null){
			$f = new AgentErrorFactory();
			self::$agentErrorFactory = $f;
			return $f;
		}
		else{
			return self::$agentErrorFactory;
		}
	}

	public static function getFileFactory(){
		if(self::$fileFactory == null){
			$f = new FileFactory();
			self::$fileFactory = $f;
			return $f;
		}
		else{
			return self::$fileFactory;
		}
	}

	public static function getHashcatReleaseFactory(){
		if(self::$hashcatReleaseFactory == null){
			$f = new HashcatReleaseFactory();
			self::$hashcatReleaseFactory = $f;
			return $f;
		}
		else{
			return self::$hashcatReleaseFactory;
		}
	}

	public static function getHashFactory(){
		if(self::$hashFactory == null){
			$f = new HashFactory();
			self::$hashFactory = $f;
			return $f;
		}
		else{
			return self::$hashFactory;
		}
	}

	public static function getHashBinaryFactory(){
		if(self::$hashBinaryFactory == null){
			$f = new HashBinaryFactory();
			self::$hashBinaryFactory = $f;
			return $f;
		}
		else{
			return self::$hashBinaryFactory;
		}
	}

	public static function getHashlistFactory(){
		if(self::$hashlistFactory == null){
			$f = new HashlistFactory();
			self::$hashlistFactory = $f;
			return $f;
		}
		else{
			return self::$hashlistFactory;
		}
	}

	public static function getHashlistAgentFactory(){
		if(self::$hashlistAgentFactory == null){
			$f = new HashlistAgentFactory();
			self::$hashlistAgentFactory = $f;
			return $f;
		}
		else{
			return self::$hashlistAgentFactory;
		}
	}

	public static function getHashTypeFactory(){
		if(self::$hashTypeFactory == null){
			$f = new HashTypeFactory();
			self::$hashTypeFactory = $f;
			return $f;
		}
		else{
			return self::$hashTypeFactory;
		}
	}

	public static function getRegVoucherFactory(){
		if(self::$regVoucherFactory == null){
			$f = new RegVoucherFactory();
			self::$regVoucherFactory = $f;
			return $f;
		}
		else{
			return self::$regVoucherFactory;
		}
	}

	public static function getSuperHashlistHashlistFactory(){
		if(self::$superHashlistHashlistFactory == null){
			$f = new SuperHashlistHashlistFactory();
			self::$superHashlistHashlistFactory = $f;
			return $f;
		}
		else{
			return self::$superHashlistHashlistFactory;
		}
	}

	public static function getTaskFileFactory(){
		if(self::$taskFileFactory == null){
			$f = new TaskFileFactory();
			self::$taskFileFactory = $f;
			return $f;
		}
		else{
			return self::$taskFileFactory;
		}
	}

	public static function getTaskFactory(){
		if(self::$taskFactory == null){
			$f = new TaskFactory();
			self::$taskFactory = $f;
			return $f;
		}
		else{
			return self::$taskFactory;
		}
	}

	public static function getSupertaskFactory(){
		if(self::$supertaskFactory == null){
			$f = new SupertaskFactory();
			self::$supertaskFactory = $f;
			return $f;
		}
		else{
			return self::$supertaskFactory;
		}
	}

	public static function getSupertaskTaskFactory(){
		if(self::$supertaskTaskFactory == null){
			$f = new SupertaskTaskFactory();
			self::$supertaskTaskFactory = $f;
			return $f;
		}
		else{
			return self::$supertaskTaskFactory;
		}
	}

	public static function getUserFactory(){
		if(self::$userFactory == null){
			$f = new UserFactory();
			self::$userFactory = $f;
			return $f;
		}
		else{
			return self::$userFactory;
		}
	}

	public static function getSessionFactory(){
		if(self::$sessionFactory == null){
			$f = new SessionFactory();
			self::$sessionFactory = $f;
			return $f;
		}
		else{
			return self::$sessionFactory;
		}
	}

	public static function getRightGroupFactory(){
		if(self::$rightGroupFactory == null){
			$f = new RightGroupFactory();
			self::$rightGroupFactory = $f;
			return $f;
		}
		else{
			return self::$rightGroupFactory;
		}
	}

	public static function getZapFactory(){
		if(self::$zapFactory == null){
			$f = new ZapFactory();
			self::$zapFactory = $f;
			return $f;
		}
		else{
			return self::$zapFactory;
		}
	}

	public static function getStoredValueFactory(){
		if(self::$storedValueFactory == null){
			$f = new StoredValueFactory();
			self::$storedValueFactory = $f;
			return $f;
		}
		else{
			return self::$storedValueFactory;
		}
	}

}
