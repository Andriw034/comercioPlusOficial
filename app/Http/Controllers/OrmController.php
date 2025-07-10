<?php

namespace App\Http\Controllers;

use App\Models\Sale;
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

          //$user = User::find(1);
          //return $user->roles;


         $sale = Sale::find(1);
         return $sale->user;

}
}