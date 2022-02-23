<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use App\Extra\General;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;

    class View extends Mongo
    {

        public $type;
        public $agent;
        public $user;
        public ?array $film = null;
        public ?ObjectId $film_id = null;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));

        }

        public function set($prop,$val):object
        {

            $this->{$prop} = $val;

            return $this;

        }

        public function setFilmId($fid = null):object
        {

            $this->film_id = $this->convertMongoId($fid);
            return $this;

        }

        public function create():?array{

            $expire = (new \DateTime())->modify('+3 months');

            $this->created = new UTCDateTime();
            $this->expire = new UTCDateTime($expire->getTimestamp() * 1000);

            return $this->dbinsert($this);

        }

    }

?>