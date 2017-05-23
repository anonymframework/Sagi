<?php

namespace Sagi\Database\Helpers;

use Sagi\Database\Validation as ValidationBase;

class Validaton
{

    use ValidationBase;

    /**
     * Validaton constructor.
     * @param array $datas
     * @param array $rules
     * @param array $filters
     */
    public function __construct(array $datas = [], array $rules = [], array $filters = [])
    {
        $this->setRules($rules)->setFilters($filters)->setDatas($datas);
    }
}

