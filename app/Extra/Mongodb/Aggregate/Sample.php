<?php

    namespace App\Extra\Mongodb\Aggregate;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;
    use App\Extra\Mongodb\Aggregate;
    use App\Extra\General;

    class Sample extends Aggregate
    {

        public $type;
        public $output;

        public function __construct()
        {

            $this->setType(strtolower(General::get_class_name(get_class($this))));

        }

        public function setLimit(int $num = 10)
        {

            $this->output[$this->type]['size'] = $num;

            return $this;

        }

        public function get():array
        {

            return $this->output;

        }

    }