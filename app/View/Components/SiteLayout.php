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
     * @param  string|null  $description  Meta description for search results and link previews.
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.site');
    }
}
