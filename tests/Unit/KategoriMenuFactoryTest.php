<?php

namespace Tests\Unit;

use App\Models\KategoriMenu;
use Tests\TestCase;

class KategoriMenuFactoryTest extends TestCase
{
    public function test_factory_can_create_more_items_than_the_default_name_pool(): void
    {
        $categories = KategoriMenu::factory()->count(6)->create();

        $this->assertCount(6, $categories);
        $this->assertDatabaseCount('kategori_menu', 6);
    }
}
