<?php

use App\Models\User;
use Pest\Plugin\Browser\BrowserPlugin;
use Pest\Plugin\Laravel\LaravelPlugin;
use Pest\Plugin\Livewire\LivewirePlugin;
use Tests\TestCase;

uses(LaravelPlugin::class, LivewirePlugin::class, BrowserPlugin::class)->in('tests');

// Browser configuration
uses(BrowserPlugin::class)->in('tests/Browser');

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| This configuration file is used by Pest to configure the testing
| framework. You can customize various aspects of Pest's behavior
| by modifying the options below.
|
| Learn more: https://pestphp.com/docs/configuration
|
*/

pest()->extend(TestCase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh --seed');
});

// Helper functions
function actingAsAdmin(): void
{
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);
}

function actingAsKasir(): void
{
    $kasir = User::factory()->kasir()->create();
    test()->actingAs($kasir);
}
