<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SiteLayout extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $title  The browser tab title.
     * @param  'public'|'auth'|'admin'  $chrome  Which header and footer the page uses.
     */
    public function __construct(
        public string $title,
        public string $chrome = 'public',
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.site');
    }
}
