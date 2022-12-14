<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Cart;
use App\Product;

class CartController extends Controller
{
  public function index()
  {
    $cart_id = Session::get('cart');
    $cart = Cart::find($cart_id);

    $total_price = 0;
    foreach ($cart->products as $product) {
      $total_price += $product->price * $product->pivot->quantity;
    }

    return view('cart.index')
      ->with('line_items', $cart->products)
      ->with('total_price', $total_price);
  }

  public function checkout()
  {
    $cart_id = Session::get('cart');
    $cart = Cart::find($cart_id);

    $cart_items = [];
    foreach($cart->product as $product){
      $line_item = [
        'name'        => $product->name,
        'description' => $product->description,
        'amount'      => $product->price,
        'currency'    => 'jpy',
        'quantity'    => $product->pivot->quantity,
      ];
      array_push($line_items, $line_item);
    }

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card'],
      'line_items'           => [$line_items],
      'success_url'          => route('product.index'),
      'cancel_url'           => route('cart.index'),
    ]);

    return view('cart.checkout',[
      'session' => $session,
      'pubblicKey' => env('STRIPE_PUBLIC_KEY')
    ]);
  }
}
