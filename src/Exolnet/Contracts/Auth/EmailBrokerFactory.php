<?php

namespace Exolnet\Contracts\Auth;

interface EmailBrokerFactory
{
    /**
     * Get a email broker instance by name.
     *
     * @param  string|null $name
     * @return mixed
     */
    public function broker($name = null);
}
