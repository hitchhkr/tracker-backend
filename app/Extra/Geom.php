<?php

    namespace App\Extra;

    class Geom
    {

        public $point;
        private $properties = [];
        public $geojson;

        public function contruct(array $point = [])
        {

            if($point){
                $this->setPoint($point);
            }

        }

        public function setPoint(array $point)
        {

            $this->point = [$point[1],$point[0]];
            $this->setGeoJson();

            return $this;

        }

        public function setGeoJson()
        {

            $json = [
                'type' => 'Feature',
                'properties' => $this->properties,
                'geometry' => $this->point
            ];

            $this->geojson = $json;

        }

        public function getGeoJson()
        {

            return $this->geojson;

        }

    }

?>