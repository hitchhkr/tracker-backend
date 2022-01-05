<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;
    use Illuminate\Hashing\BcryptHasher;

    class Users extends Mongo {

        public $username;
        public $password;
        public $name;
        public $email;
        public $active = true;
        public $superAdmin = false;
        private ObjectId $id;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));

        }

        public function set($prop,$val){

            if($val){
                $this->{$prop} = trim($val);
            }else{
                $this->{$prop} = null;
            }

            return $this;

        }

        public function setId(string $id)
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function getUserByUsername():?array{

            $q = [
                'username' => $this->username
            ];

            return $this->dbfind($q,false);

        }

        public function getUserByEmail():?array{

            $q = [
                'email' => $this->email
            ];

            return $this->dbfind($q,false);

        }

        public function remove(){

            if(!$this->id){
                throw new \Exception('No id was provided');
            }

            return $this->dbdelete([
                '_id' => $this->id
            ]);

        }

        public function fetch(?string $id = null):?array{

            $q = [];
            $multi = true;

            $options = [
                'sort' => [
                    'username' => 1
                ]
            ];

            if($id){
                $q = [
                    '_id' => $this->convertMongoId($id)
                ];
                $multi = false;
            }

            return $this->dbfind($q,$multi,$options);

        }

        public function create():?array{

            $check = $this->getUserByUsername();

            if($check){
                return null;
            }

            $this->created = new UTCDateTime();

            return $this->dbinsert($this);

        }

        public function updatePreferences($type,$vars)
        {

            if(!$this->id){
                throw new \Exception('No id was provided');
            }

            $q = [
                '_id' => $this->id
            ];

            $update = [
                '$set' => [
                    $type => $vars,
                    $type . 'Updated' => new UTCDateTime()
                ]
            ];

            return $this->dbupdate($q,$update);

        }

        public function reset()
        {

            if(!$this->email){
                return null;
            }

            $rid = (new BcryptHasher)->make($this->email);
            $now = new \DateTime();
            $now->modify('+3 hours');

            $q = [
                'email' => $this->email
            ];

            $update = [
                '$set' => [
                    '_resetid' => $rid,
                    'lastReset' => new UTCDateTime(),
                    'resetValidUntil' => new UTCDateTime($now->getTimestamp() * 1000)
                ],
                '$inc' => [
                    'resetRequests' => 1
                ]
            ];

            $db = $this->dbupdate($q,$update);

            return $rid;

        }

        public function checkValidToken():bool
        {
            if(!$this->email){
                return false;
            }
            
            $now = new UTCDateTime();

            $q = [
                'email' => $this->email,
                'resetValidUntil' => [
                    '$gte' => $now
                ]
            ];

            $result = $this->dbfind($q,true);

            if($result){
                return true;
            }else{
                return false;
            }
        }

        public function updatePassword($pwd)
        {

            if(!$this->email){
                return null;
            }

            $q = [
                'email' => $this->email
            ];

            $user = $this->getUserByEmail();

            $update = [
                '$set' => [
                    'password' => $pwd,
                    'passwordLastUpdated' => new UTCDateTime(),
                ],
                '$unset' => [
                    '_resetid' => ''
                ],
                '$push' => [
                    'passwordHistory' => [
                        'password' => $user['password'],
                        'changed' => new UTCDateTime()
                    ]
                ]
            ];

            return $this->dbupdate($q,$update);

        }

    }

?>