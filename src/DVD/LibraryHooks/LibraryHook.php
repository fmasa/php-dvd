<?php

namespace DVD\LibraryHooks;

/**
 * Library hook interface.
 */
interface LibraryHook
{
    /**
     * @var string Enabled status for a hook.
     */
    const ENABLED = 'ENABLED';

    /**
     * @var string Disabled status for a hook.
     */
    const DISABLED = 'DISABLED';

    /**
     * Enables library hook which means that all of this library
     * http interactions are intercepted.
     *
     * @param \Closure Callback which will be called when a request is intercepted.
     * @return void
     *@throws \DVD\DVDException When specified callback is not callable.
     */
    public function enable(\Closure $requestCallback);

    /**
     * Disables library hook, so no http interactions of
     * this library are intercepted anymore.
     *
     * @return void
     */
    public function disable();

    /**
     * Returns true if library hook is enabled, false otherwise.
     *
     * @return boolean True if library hook is enabled, false otherwise.
     */
    public function isEnabled();
}
