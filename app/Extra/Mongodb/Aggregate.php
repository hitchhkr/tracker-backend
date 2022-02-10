<?php

    namespace App\Extra\Mongodb;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;

    abstract class Aggregate
    {

        public $type;

        public function setType(string $type)
        {

            $this->type = '$' . $type;

            return $this;

        }

    }