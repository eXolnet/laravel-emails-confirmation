<?php

namespace Exolnet\Foundation\Auth;

trait RedirectsOuts
{
    /**
     * Get the logout redirect path.
     *
     * @return string
     */
    public function redirectOutPath()
    {
        if (method_exists($this, 'redirectOutTo')) {
            return $this->redirectOutTo();
        }

        if (property_exists($this, 'redirectOutTo')) {
            return $this->redirectOutTo;
        }

        return redirect('/');
    }
}
