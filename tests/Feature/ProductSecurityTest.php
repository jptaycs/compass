<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductSecurityTest extends TestCase
{
    use RefreshDatabase;

    private array $uploaded = [];

    protected function tearDown(): void
    {
        foreach ($this->uploaded as $name) {
            @unlink(public_path('img/' . $name));
        }
        parent::tearDown();
    }

    private function user(bool $admin): User
    {
        // isAdmin is not mass assignable (registration must never set it), so it is forced here.
        return User::forceCreate([
            'name' => $admin ? 'Admin' : 'Customer',
            'email' => ($admin ? 'admin' : 'customer') . '@example.com',
            'password' => bcrypt('secret-password'),
            'isAdmin' => $admin,
        ]);
    }

    private function product(User $owner): Product
    {
        return Product::create(['product_name' => 'Tour', 'price' => 100, 'img' => 'boracay.png', 'user_id' => $owner->id]);
    }

    public function test_guests_cannot_reach_product_management()
    {
        $product = $this->product($this->user(true));

        $this->get('/shop/add')->assertRedirect('/login');
        $this->post('/shop/store', ['product_name' => 'X', 'product_price' => 1])->assertRedirect('/login');
        $this->get("/shop/edit/{$product->id}")->assertRedirect('/login');
        $this->patch("/shop/update/{$product->id}", ['product_name' => 'X', 'product_price' => 1])->assertRedirect('/login');
        $this->delete("/shop/delete/{$product->id}")->assertRedirect('/login');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'product_name' => 'Tour']);
    }

    public function test_non_admins_are_forbidden()
    {
        $product = $this->product($this->user(true));
        $customer = $this->user(false);

        $this->actingAs($customer)->get('/shop/add')->assertForbidden();
        $this->actingAs($customer)->delete("/shop/delete/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_an_admin_cannot_change_another_admins_product()
    {
        $product = $this->product($this->user(true));
        $other = User::forceCreate(['name' => 'Other', 'email' => 'other@example.com', 'password' => bcrypt('x'), 'isAdmin' => true]);

        $this->actingAs($other)->delete("/shop/delete/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_a_php_file_cannot_be_uploaded()
    {
        $admin = $this->user(true);
        $shell = UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]);');

        $this->actingAs($admin)
            ->post('/shop/store', ['product_name' => 'X', 'product_price' => 1, 'img' => $shell])
            ->assertSessionHasErrors('img');
        $this->assertFileDoesNotExist(public_path('img/shell.php'));
        $this->assertDatabaseCount('products', 0);
    }

    public function test_an_admin_can_add_a_product_with_an_image_saved_under_a_random_name()
    {
        $admin = $this->user(true);
        $image = UploadedFile::fake()->image('../../evil name.png');

        $this->actingAs($admin)
            ->post('/shop/store', ['product_name' => 'Island hop', 'product_price' => 2500, 'img' => $image])
            ->assertRedirect('shop');

        $product = Product::firstOrFail();
        $this->uploaded[] = $product->img;
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}\.png$/', $product->img);
        $this->assertFileExists(public_path('img/' . $product->img));
        $this->assertSame($admin->id, $product->user_id);
    }

    public function test_an_admin_can_update_and_delete_their_own_product()
    {
        $admin = $this->user(true);
        $product = $this->product($admin);

        $this->actingAs($admin)
            ->patch("/shop/update/{$product->id}", ['product_name' => 'Renamed', 'product_price' => 150])
            ->assertRedirect('shop');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'product_name' => 'Renamed', 'img' => 'boracay.png']);

        $this->actingAs($admin)->delete("/shop/delete/{$product->id}")->assertRedirect('shop');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
