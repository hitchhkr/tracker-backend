<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use App\Extra\General;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;

    class Audit extends Mongo
    {

        public $type;
        public $agent;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));

        }

        public function set($prop,$val){

            $this->{$prop} = $val;

            return $this;

        }

        public function create():?array{


            $this->created = new UTCDateTime();

            return $this->dbinsert($this);

        }

    }

?>