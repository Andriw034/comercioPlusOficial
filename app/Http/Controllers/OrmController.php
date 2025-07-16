<?php

namespace App\Http\Controllers;
use App\Models\Claim;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class OrmController extends Controller
{
    public function consulta(){
       // $user = User::find(2);
        //return $user->products;

        //$user = User::find(2);
        //return $user->sales;

       // $user = User::find(2);
        // return $user->locations;

        //$user = User::find(2);
       // return $user->ratings;

        //$user = User::find(2);
       //return $user->notifications;

       //$user = User::find(1);
       //return $user->setting;

      //$user = User::find(1);
      //return $user->roles;

        // $setting = Setting::find(1);
        // return $setting->user;

        //$sale = Sale::find(1);
        // return $sale->user;

     
       // $sale = Sale::find(1);
        // return $sale->product;

      //  $sale = Sale::find(1);
      //  return $sale->location;

        
     //  $role = Role::find(1);
     //    return $role->users;

      // $rating = Rating::find(1);
      //  return $rating->user;
       //$rating = Rating::find(1);
      // return $rating->product;

       //$profile = Profile::find(1);
       //return $profile->user;
        //$rating = Rating::find(1);
       // return $rating->product;
       
       //  $product = Product::find(1);
       // return $product->user;
         // $product = Product::find(1);
       /// return $product->sales;
       // $product = Product::find(1);
        //return $product->ratings;

       // $product = Product::find(1);
       // return $product->category;
       // $product = Product::find(1);
      //  return $product->orderproduct;

     //  $product = Product::find(1);
     //  return $product->cartproducts;

    // $order = Order::find(1);
   // return $order->user;
// $order = Order::find(1);
 //   return $order->ordenproducts;

    //$orderProducto = OrderProduct::find(1);
    // return $orderProducto->order;
      //$orderProducto = OrderProduct::find(1);
     //return $orderProducto->product;

    // $notification = Notification::find(1);
    // return $notification->user;
    // $notification = Notification::find(1);
   //  return $notification->user;

    // $location = Location::find(1);
      // return $location->user;

     // $location = Location::find(1);
      //return $location->sales;

      $claim = Claim::find(1);
        return $claim->user;
      
}
}
