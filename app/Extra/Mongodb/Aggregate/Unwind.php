<?php

    namespace App\Extra\Mongodb\Aggregate;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;
    use App\Extra\Mongodb\Aggregate;
    use App\Extra\General;

    class Unwind extends Aggregate
    {

        public $type;
        public $output;

        public function __construct()
        {

            $this->setType(strtolower(General::get_class_name(get_class($this))));

        }

        public function setField(string $str)
        {

            $this->output[$this->type]['path'] = '$' . $str;

            return $this;

        }

        public function get():array
        {

            return $this->output;

        }

    }