<?php

    namespace App\Models;

    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Rating
    {

        public float $quality;
        public float $enjoyment;
        public float $difficulty;
        public string $review;

        public function __construct(){

            $this->setQuality();
            $this->setEnjoyment();
            $this->setDifficulty();

        }

        public function setQuality(float $val = 0)
        {
            $this->quality = $val;
            return $this;
        }

        public function setEnjoyment(float $val = 0)
        {
            $this->enjoyment = $val;
            return $this;
        }

        public function setDifficulty(float $val = 0)
        {
            $this->difficulty = $val;
            return $this;
        }

        public function setReview(string $val = '')
        {
            $this->review = $val;
            return $this;
        }

        public function setAll(array $vals)
        {

            $this->setQuality($vals['quality']);
            $this->setEnjoyment($vals['enjoyment']);
            $this->setDifficulty($vals['difficulty']);
            $this->setReview($vals['review']);

            return $this;

        }

    }

?>