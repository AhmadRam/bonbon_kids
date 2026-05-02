<?php

namespace Webkul\Core\Repositories;

use Webkul\Core\Eloquent\Repository;

class CountryCityRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model()
    {
        return 'Webkul\Core\Contracts\CountryCity';
    }
}
