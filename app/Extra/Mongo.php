<?php

    namespace App\Extra;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;

    class Mongo {

        protected $db;
		protected $client;
		protected $uri;
		protected $uriOptions;
		protected $driverOptions;
		protected $coll;
		
		public function __construct(){

			$this->setDBOptions(env('MONGO_URI'));
			
			try{
				$this->createClient();
				$this->db = $this->client->selectDatabase(env('MONGO_COLL'));
			}catch(Exception $e){
				echo '<b>MongoDB error:</b> ' . $e;
			}catch(\MongoDB\Driver\Exception\ConnectionTimeoutException $e){
				echo '<b>MongoDB error:</b> ' . $e->getMessage();
			}catch(\MongoDB\Driver\Exception\TypeError $e){
				echo '<b>MongoDB error:</b> ' . $e->getMessage();
			}
			
		}

		protected function setCollection($coll){

			$this->coll = $coll;

			return $this;

		}

		protected function createClient(){

			$this->client = new Client(
				$this->uri,
				$this->uriOptions,
				$this->driverOptions
			);

		}

		protected function setDBOptions($uri){

			//echo $uri;

		//	Main URI for the mongo server
			$this->uri = $uri;
		//	Extra URI options here including read preferences for mongo clusters	
			$this->uriOptions = [];
		//	Additional options returning response as PHP arrays
			$this->driverOptions = [
				'typeMap' => [
					'root' => 'array', 
					'document' => 'array', 
					'array' => 'array'
				]
			];

			return $this;

		}
		
		public function dbinsert($data,$many = false){

			$type = 'insertOne';

			if($many == true){
				$type = 'insertMany';
			}

			$coll = $this->coll;

			try{

				$result = $this->db->$coll->$type($data);

				if($many == true){
					$id = $result->getInsertedIds();
				}else{
					$id = $result->getInsertedId();
				}

				return [
					'id' => $id,
					'num' => $result->getInsertedCount()
				];

			}catch(MongoDB\Exception\Exception $e){

				return $e->getMessage();

			}catch(MongoDB\Driver\Exception\BulkWriteException $e){

				return $e->getMessage();

			}

		}

		public function dbupdate($q,$data,$many = false){

			$type = 'updateOne';

			if($many == true){
				$type = 'updateMany';
			}

			$coll = $this->coll;

			try{

				$update = $this->db->$coll->$type($q,$data);

				return [
					'matched' => $update->getMatchedCount(),
					'modified' => $update->getModifiedCount()
				];


			}catch(MongoDB\Exception\Exception $e){

				return $e->getMessage();

			}

		}

		public function dbdelete($q = [],$many = false){

			$type = 'deleteOne';

			if($many == true){
				$type = 'deleteMany';
			}

			$coll = $this->coll;

			$result = $this->db->$coll->$type($q);

			return $result->getDeletedCount();

		}

		public function dbfind($q = [],$many = false,$options = []){

			$type = 'findOne';

			if($many == true){
				$type = 'find';
			}

			$coll = $this->coll;

			try{

				$result = $this->db->$coll->$type($q,$options);

				if($many == true){
					$data = [];
					foreach($result AS $doc){
						$data[] = $doc;
					}
					return $data;
				}else{
					return $result;
				}

			}catch(MongoDB\Driver\Exception\Exception $e){

				return $e->getMessage();

			}catch(Exception $e){

				return $e->getMessage();

			}

		}

		public function dbagg($agg){

			$data = [];

			$coll = $this->coll;

			try{

				$result = $this->db->$coll->aggregate($agg);

				foreach($result AS $doc){

					$data[] = $doc;

				}

				return $data;

			}catch(MongoDB\Driver\Exception\Exception $e){

				return $e->getMessage();

			}

		}
		
		public function checkMongoID($id){
			
			if ($id instanceof \MongoDB\BSON\ObjectID){
				return true;
			}
			
			return false;
			
		}
		
		public function convertMongoId($id){

			try{
			
				if($this->checkMongoID($id) == true){
					return $id;
				}else{
					return new \MongoDB\BSON\ObjectId($id);
				}

			}catch(\MongoDB\Driver\Exception\InvalidArgumentException $e){
				return null;
			}
			
		}

		function getfileBlob($file){
			
			$blob = '';
			
			$start = 0;
			$end = $file['length'] - 1;
			$stream = $this->dlFile($file['_id'],true);
			while (!feof($stream) && ($p = ftell($stream)) <= $end) {
				$blob .= fread($stream, (1024 * 8));
				ob_flush();
				//flush();
			}

			return $blob;
			
		}

		function dlFile($id,$stream = false){

			$fileId = $this->convertMongoId($id);

			try{

				$stream = $this->bucket->openDownloadStream($fileId);

				if($stream == true){
					return $stream;
				}else{
					return stream_get_contents($stream);
				}

			}catch(MongoDB\Exception\Exception $e){

				return $e->getMessage();

			}

        }

		function formatForInput(array $vars):array
		{

			$output = [];

			foreach($vars AS $k => $v){

				if($v instanceof \DateTime){
					$output[$k]  = new UTCDateTime($v->getTimestamp() * 1000);
					continue;
				}

				if(\DateTime::createFromFormat('Y-m-d',$v)){
					$dt = \DateTime::createFromFormat('Y-m-d',$v);
					$dt->setTime(0,0,0);
					$output[$k]  = new UTCDateTime($dt->getTimestamp() * 1000);
					continue;
				}

				$output[$k] = $v;

			}

			return $output;

		}

    }

?>