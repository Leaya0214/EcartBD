@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Payment Successful</h1>
    <p>Your order has been placed successfully. Order ID: {{ $order_id }}</p>
</div>
@endsection
