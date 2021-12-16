<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

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

    }

?>