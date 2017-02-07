<?php

namespace Mattobell\Shipping\Api\Provider;


class ShippingRateCaculator
{
    const BASE_PRICE = 650;

    const PER_KILOGRAM = 100;

    const MAX_WEIGHT   = 10;

    const ZONE_ONE_PLUS = 2;


    protected function calculateCost($weight = 0.5)
    {
        //TODO::fix when the weight is 0.5 kg
        if ($weight <= 0.5) {
            return self::BASE_PRICE;
        }
        for ($i = 0.5; $i <= self::MAX_WEIGHT; $i = $i + 0.5) {
            $increment = floor($i) * self::PER_KILOGRAM - 50;
            if ($weight == $i) {
                $cost = $increment + self::BASE_PRICE;
                return $cost;
            }
        }

    }

    public  function calculate($weight, $postal_code)
    {
        if (in_array($postal_code, $this->zoneOneExclusionList())) {
            return $this->calculateCost($weight) * self::ZONE_ONE_PLUS;
        }
        $zoneOnePlus = $this->calculateCost($weight);
        return $zoneOnePlus;
    }

    public function zoneOneExclusionList()
    {
        return ['103211', '103241', '103242', '103251', '103261', '104211', '104212', '104213', '104214', '104221', '104222', '104223', '104224', '104225', '104231', '104232', '104233', '106101'];
    }

}


//echo ShippingRateCaculator::calculate(7);
