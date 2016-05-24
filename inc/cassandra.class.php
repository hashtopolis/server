<?php
/**
 * This class manages the connection and queries on the Cassandra server
 * for Crawly.
 * 
 * @author Sein
 */

class CrawlyCassandra {
	public static $loginUser = "crawly";
	public static $loginPassword = "F97673gKJGKFGkfeiz67352";
	
	private $statementInsertDomain; //insert new domain
	private $statementUpdateDomain; //update domain data
	private $statementDeleteDomain; //delete domain
	private $statementSelectDomain; //select data by domain
	private $statementSelectDomainChunk; //select data by chunk
	private $statementInsertReference;
	private $statementDeleteReference;
	private $statementSelectReferenceFrom;
	private $statementSelectReferenceTo;
	
	private function prepareStatements(){
		$this->statementInsertDomain = $this->session->prepare("INSERT INTO Crawl (domainName, httpStatus, time, generatorMeta, chunkId) VALUES (?, ?, ?, ?, ?)");
		$this->statementUpdateDomain = $this->session->prepare("UPDATE Crawl SET httpStatus=?, time=?, generatorMeta=?, chunkId=? WHERE domainName=?");
		$this->statementDeleteDomain = $this->session->prepare("DELETE FROM Crawl WHERE domainName=?");
		$this->statementSelectDomain = $this->session->prepare("SELECT * FROM Crawl WHERE domainName=?");
		$this->statementSelectDomainChunk = $this->session->prepare("SELECT * FROM Crawl WHERE chunkId=?");
		$this->statementInsertReference = $this->session->prepare("INSERT INTO Reference (id, fromDomain, toDomain) VALUES (?, ?, ?)");
		$this->statementDeleteReference = $this->session->prepare("DELETE FROM Reference WHERE id=?");
		$this->statementSelectReferenceFrom = $this->session->prepare("SELECT * FROM Reference WHERE fromDomain=?");
		$this->statementSelectReferenceTo = $this->session->prepare("SELECT * FROM Reference WHERE toDomain=?");
	}
	
	public function replaceDomain($domainold, $domainnew){
		$res = $this->selectDomain($domainold);
		$this->deleteDomain($res['domainname']);
		$this->insertDomain(array(array($domainnew, $res['httpstatus'], $res['time'], $res['generatormeta'], $res['chunkid'])));
	}
	
	public function __construct(){
		$cluster  = Cassandra::cluster()->withPersistentSessions(false)->withCredentials(CrawlyCassandra::$loginUser, CrawlyCassandra::$loginPassword)->build();
		$keyspace  = 'crawly';
		$this->session = $cluster->connect($keyspace);
		$this->prepareStatements();
	}
	
	public function getPointer(){
		return $this->session;
	}
	
	public function updateDomain($list){
		$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
		$count = 0;
		foreach($list as $entry){
			$values = array("domainName" => $entry[0], "httpStatus" => (int)$entry[1], "time" => (int)$entry[2], "generatorMeta" => $entry[3], "chunkId" => (int)$entry[4]);
			$batch->add($this->statementUpdateDomain, $values);
			$count++;
			if($count > 500){
				try{
					$this->session->execute($batch);
					$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
					$count = 0;
				}
				catch(Exception $e){
					die("update: ".$e->getMessage()."\n");
				}
			}
		}
		try{
			$this->session->execute($batch);
			return true;
		}
		catch(Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function selectDomain($domain){
		$values = array("domainName" => $domain);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
	
		try{
			$result = $this->session->execute($this->statementSelectDomain, $options);
			if(sizeof($result) > 0){
				return $result[0];
			}
		}
		catch(Exception $e){
			return false;
		}
		return false;
	}
	
	public function selectDomainChunk($chunkId){
		$values = array("chunkId" => (int)$chunkId);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values, 'timeout' => 60));
	
		try{
			$result = $this->session->execute($this->statementSelectDomainChunk, $options);
			if(sizeof($result) > 0){
				return $result;
			}
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function selectReferenceFrom($domain){
		$values = array("fromDomain" => $domain);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
	
		try{
			$result = $this->session->execute($this->statementSelectReferenceFrom, $options);
			if(sizeof($result) > 0){
				return $result;
			}
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function selectReferenceTo($domain){
		$values = array("toDomain" => $domain);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
	
		try{
			$result = $this->session->execute($this->statementSelectReferenceTo, $options);
			if(sizeof($result) > 0){
				return $result;
			}
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function deleteDomain($domain){
		$values = array("domainName" => $domain);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
		
		try{
			$this->session->execute($this->statementDeleteDomain, $options);
			return true;
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function deleteReference($refId){
		$values = array("id" => $refId);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
	
		try{
			$this->session->execute($this->statementDeleteReference, $options);
			return true;
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function insertDomain($list){
		$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
		$count = 0;
		foreach($list as $entry){
			$values = array("domainName" => $entry[0], "httpStatus" => (int)$entry[1], "time" => (int)$entry[2], "generatorMeta" => $entry[3], "chunkId" => (int)$entry[4]);
			$batch->add($this->statementInsertDomain, $values);
			$count++;
			if($count > 500){
				try{
					$this->session->execute($batch);
					$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
					$count = 0;
				}
				catch(Exception $e){
					die("insertDomain: ".$e->getMessage()."\n");
				}
			}
		}
		try{
			$this->session->execute($batch);
			return true;
		}
		catch(Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
	
	public function insertReference($list){
		$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
		$count = 0;
		foreach($list as $entry){
			$values = array("id" => $entry[0], "fromDomain" => $entry[1], "toDomain" => $entry[2]);
			$batch->add($this->statementInsertReference, $values);
			$count++;
			if($count > 300){
				try{
					$this->session->execute($batch);
					$batch = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
					$count = 0;
				}
				catch(Exception $e){
					die("insertReference: ".$e->getMessage()."\n");
				}
			}
		}
		try{
			$this->session->execute($batch);
			return true;
		}
		catch(Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
		
		$values = array("id" => $id, "fromDomain" => $fromDomain, "toDomain" => $toDomain);
		$options = new Cassandra\ExecutionOptions(array('arguments' => $values));
	
		try{
			$this->session->execute($this->statementInsertReference, $options);
			return true;
		}
		catch(\Exception $e){
			die($e->getMessage()."\n");
		}
		return false;
	}
}





