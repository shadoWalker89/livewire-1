<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class LivewireTestingTest extends TestCase
{
    /** @test */
    public function test_method_accepts_arguments_to_pass_to_mount()
    {
        $component = app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo');

        $this->assertStringContainsString('foo', $component->dom);
    }

    /** @test */
    public function test_set_multiple_with_array()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo')
            ->set(['name' => 'bar'])
            ->assertSet('name', 'bar');
    }

    /** @test */
    public function test_assert_set()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo')
            ->assertSet('name', 'foo');
    }

    /** @test */
    public function test_assert_see()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'should see me')
            ->assertSee('should see me');
    }

    /** @test */
    public function test_assert_see_doesnt_include_json_encoded_data_put_in_wire_data_attribute()
    {
        // See for more info: https://github.com/calebporzio/livewire/issues/62
        app(LivewireManager::class)
            ->test(HasMountArgumentsButDoesntPassThemToBladeView::class, 'shouldnt see me')
            ->assertDontSee('shouldnt see me');
    }

    /** @test */
    public function test_assert_emitted()
    {
        app(LivewireManager::class)
            ->test(EmitsEventsComponentStub::class)
            ->call('emitFoo')
            ->assertEmitted('foo')
            ->call('emitFooWithParam', 'bar')
            ->assertEmitted('foo', 'bar')
            ->call('emitFooWithParam', 'baz')
            ->assertEmitted('foo', function ($event, $params) {
                return $event === 'foo' && $params === ['baz'];
            });
    }
}

class HasMountArguments extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('show-name');
    }
}

class HasMountArgumentsButDoesntPassThemToBladeView extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class EmitsEventsComponentStub extends Component
{
    public function emitFoo()
    {
        $this->emit('foo');
    }

    public function emitFooWithParam($param)
    {
        $this->emit('foo', $param);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
