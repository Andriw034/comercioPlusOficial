<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;

class PruebaController extends Controller
{
    public function prueba(){
        $user = User::find(1);
        return $user->products;
    }

    //   public function prueba(){
    //     $user = User::find(1);
    //     return $user-> notifications;
    // }

    //   public function prueba(){
    //     $user = User::find(1);
    //     return $user->roles;
    // }

    // public function prueba(){
    //     $cart = Cart::find(2);
    //     return $cart->user;
    // }

    //  public function prueba(){
    //     $carproducto = CartProduct::find(2);
    //     return $carproducto->cart;
    //     // return $carproducto->product;
    // }

    
    // public function prueba(){
    //      $ordenProudct = OrderProduct::find(2);
    //     // return $ordenProudct->product;
    //      return $ordenProudct->order;

    //  }


    // public function prueba(){
    //      $orden = Order::find(2);
    //      return $orden->ordenproducts;
    //  }


    // public function prueba(){
    //      $roles = Role::all();
    //      return $roles;
    //   }


    
    //  public function prueba(){
    //       $profile = Profile::find(2);
    //       return $profile->user;
    //    }


//   public function prueba()
// {
//     $sale = Sale::find(21);
//     if (!$sale) {
//         return response()->json(['error' => 'Venta no encontrada'], 404);
//     }
//     return $sale->user;
// }


  //  public function prueba(){
  //         $notificaciones = Notification::find(2);
  //         return $notificaciones->user;
  //      }


}
