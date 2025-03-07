<?php

namespace Tests;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\LivewireManager;

class PublicPropertiesAreAvailableInTheViewTest extends TestCase
{
    /** @test */
    public function public_property_is_accessible_in_view()
    {
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewStub::class);

        $this->assertTrue(Str::contains(
            $component->dom,
            'Caleb'
        ));
    }
}

class PublicPropertiesInViewStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
