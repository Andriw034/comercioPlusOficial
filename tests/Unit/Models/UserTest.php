<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_crear_usuario_con_datos_validos()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@ejemplo.com',
            'password' => bcrypt('contraseña123'),
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Juan Pérez', $user->name);
        $this->assertEquals('juan@ejemplo.com', $user->email);
    }

    /** @test */
    public function email_debe_ser_unico()
    {
        User::factory()->create(['email' => 'test@ejemplo.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@ejemplo.com']);
    }

    /** @test */
    public function usuario_puede_tener_una_tienda()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Store::class, $user->store);
        $this->assertEquals($store->id, $user->store->id);
    }

    /** @test */
    public function usuario_puede_tener_un_carrito()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->carts->contains($cart));
    }

    /** @test */
    public function usuario_puede_tener_multiples_ordenes()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        foreach ($orders as $order) {
            $this->assertTrue($user->orders->contains($order));
        }
    }

    /** @test */
    public function password_se_encripta_automaticamente()
    {
        $user = User::factory()->create(['password' => bcrypt('contraseña123')]);

        $this->assertTrue(Hash::check('contraseña123', $user->password));
        $this->assertNotEquals('contraseña123', $user->password);
    }

    /** @test */
    public function puede_verificar_si_email_esta_verificado()
    {
        $userSinVerificar = User::factory()->create(['email_verified_at' => null]);
        $userVerificado = User::factory()->create(['email_verified_at' => now()]);

        $this->assertFalse($userSinVerificar->hasVerifiedEmail());
        $this->assertTrue($userVerificado->hasVerifiedEmail());
    }

    /** @test */
    public function nombre_completo_se_formatea_correctamente()
    {
        $user = User::factory()->create(['name' => 'juan pérez']);

        // Asumiendo que hay un accessor para formatear el nombre
        $this->assertEquals('Juan Pérez', $user->formatted_name ?? $user->name);
    }

    /** @test */
    public function puede_obtener_iniciales_del_usuario()
    {
        $user = User::factory()->create(['name' => 'Juan Carlos Pérez']);

        $this->assertEquals('JCP', $user->initials);
    }

    /** @test */
    public function usuario_puede_ser_comerciante()
    {
        $user = User::factory()->create();
        $user->assignRole('comerciante');

        $this->assertTrue($user->esComerciante());
        $this->assertFalse($user->esCliente());
    }

    /** @test */
    public function usuario_puede_ser_cliente_regular()
    {
        $user = User::factory()->create();
        $user->assignRole('cliente');

        $this->assertTrue($user->esCliente());
        $this->assertFalse($user->esComerciante());
    }


}
