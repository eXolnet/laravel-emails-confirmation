<?php

namespace Exolnet\Foundation\Auth;

trait RedirectsFailures
{
    /**
     * Get the failure redirect path.
     *
     * @return string
     */
    public function redirectFailurePath()
    {
        if (method_exists($this, 'redirectFailureTo')) {
            return $this->redirectFailureTo();
        }

        if (property_exists($this, 'redirectFailureTo')) {
            return $this->redirectFailureTo;
        }

        return redirect()->getUrlGenerator()->previous();
    }
}
