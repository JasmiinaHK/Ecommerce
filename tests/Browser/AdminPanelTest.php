<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminPanelTest extends DuskTestCase
{
    /**
     * Test admin orders page
     *
     * @return void
     */
    public function testAdminOrdersPage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/orders')
                    ->assertSee('Orders')
                    ->assertVisible('table.dataTable')
                    ->assertScript('return typeof $.fn.DataTable === \'function\'')
                    ->screenshot('admin-orders-page');
        });
    }

    /**
     * Test admin products page
     *
     * @return void
     */
    public function testAdminProductsPage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/products')
                    ->assertSee('Products')
                    ->assertVisible('table.dataTable')
                    ->screenshot('admin-products-page');
        });
    }

    /**
     * Test admin categories page
     *
     * @return void
     */
    public function testAdminCategoriesPage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/categories')
                    ->assertSee('Categories')
                    ->assertVisible('table.dataTable')
                    ->screenshot('admin-categories-page');
        });
    }

    /**
     * Test admin login
     *
     * @return void
     */
    public function testAdminLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'admin@example.com') // Update with your admin email
                    ->type('password', 'password') // Update with your admin password
                    ->press('Login')
                    ->assertPathIs('/admin/dashboard')
                    ->screenshot('admin-login');
        });
    }
}
