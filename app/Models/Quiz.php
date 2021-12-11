<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;
    use App\Models\Users;
    use App\Models\Round;

    class Quiz extends Mongo {

        private ObjectId $id;
        //public $draft = true;
        public $active = false;
        public $title;
        public $startdate;
        public $_teamid;
        public $rounds = [];

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));

        }

        public function set($prop,$val){

            if($val instanceof \DateTime){
                $this->{$prop} = new UTCDateTime($val->getTimestamp() * 1000);
            }else{
                $this->{$prop} = $val;
            }

            return $this;

        }

        public function setId(string $id)
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function setTeamId(string $id)
        {
            $this->_teamid = $this->convertMongoId($id);
        
            return $this;
        }

        public function setTeamIdByUsername(string $username)
        {

            $user = new Users();
            $user->set('username',$username);
            $result = $user->getUserByUsername();

            if(!$result){
                throw new Exception('No user could be found');
            }

            $this->_teamid = $result['_id'];

            return $this;

        }

        public function get()
        {
            $multi = true;
            $q = [
                'active' => $this->active,
                //'draft' => $this->draft
            ];

            if(isset($this->id)){
                $multi = false;
                $q = [
                    '_id' => $this->id
                ];
            }

            if($this->_teamid){
                $q = [
                    '_teamid' => $this->_teamid
                ];
            }

            $options = [
                'sort' => [
                    'startdate' => -1
                ]
            ];

            return $this->dbfind($q,$multi,$options);

        }

        public function create()
        {

            if(!$this->_teamid){
                throw new Exception('No team id has been set');
            }

            $this->created = new UTCDateTime();

            return $this->dbinsert($this);

        }

        public function addRound(Round $round)
        {

            if(!$this->id){
                throw new Exception('No id has been set');
            }

            $q = [
                '_id' => $this->convertMongoId($this->id)
            ];

            $update = [
                '$push' => [
                    'rounds' => $round
                ],
                '$set' => [
                    'updated' => new UTCDateTime()
                ],
                '$inc' => [
                    'version' => 1
                ]
            ];

            return $this->dbupdate($q,$update);

        }

        public function update($vars){

            if(!$this->id){
                throw new Exception('No id');
            }

            $q = [
                '_id' => $this->id
            ];

            $vars['updated'] = new UTCDateTime();

            $update = [
                '$set' => $vars,
                '$inc' => [
                    'version' => 1
                ]
            ];

            return $this->dbupdate($q,$update);

        }

        public function remove()
        {

            if(!$this->id){
                throw new Exception('No id');
            }

            $q = [
                '_id' => $this->id
            ];

            return $this->dbdelete($q);

        }

    }

?>