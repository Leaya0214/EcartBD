@extends('frontend.template.layout')
@section('content')
<section class="cart_section section_space">
    <div class="container">
    {{-- Toastr notifications (CSS loaded globally in includes.css) --}}
        @if(session('message'))
            <input type="hidden" id="session-message" value="{{ e(session('message')) }}">
        @endif
        @if(session('error'))
            <input type="hidden" id="session-error" value="{{ e(session('error')) }}">
        @endif
        {{-- Notice via query param will show a popup even if session cookie is lost during external redirect --}}
        @if(request()->has('notice'))
            <input type="hidden" id="checkout-notice" value="{{ e(request('notice')) }}">
        @endif
        @if(count($items) > 0)
        <div class="cart_table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subtotal = 0;
                        $delivery = 20;
                        $discount = 0;
                    @endphp
                    
                    @foreach ($items as $item)
                        @php
                            $itemTotal = $item['price'] * $item['quantity'];
                            $subtotal += $itemTotal;
                            $discount += $item['discount'];
                        @endphp
                        <tr>
                            <td>
                                <div class="cart_product">
                                    <img src="{{ asset('backend/product/' . $item['image']) }}" alt="{{ $item['name'] }}">
                                </div>
                            </td>
                            <td class="text-center"><span class="price_text">৳{{ number_format($item['price'], 2) }}</span></td>
                            <td class="text-center">
                                {{-- <form action="{{ route('cart.update', $item['id']) }}" method="POST"> --}}
                                    {{-- @csrf --}}
                                    <div class="quantity_input">
                                        <button type="button" id="input_number_decrement-{{$item['id']}}" class="" data-id="{{$item['id']}}">
                                            <i class="fal fa-minus"></i>
                                        </button>
                                        <input class="" id="input_number_2-{{$item['id']}}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" data-id="{{ $item['id'] }}">
                                        <button type="button" id="input_number_increment-{{$item['id']}}" class="" data-id="{{$item['id']}}">
                                            <i class="fal fa-plus"></i>
                                        </button>
                                    </div>
                                {{-- </form> --}}
                            </td>
                            <td class="text-center"><span id="item-total-{{$item['id']}}" class="price_text">৳{{ number_format($itemTotal, 2) }}</span></td>
                            <td class="text-center">
                                {{-- <form action="{{ route('cart.remove', $item['id']) }}" method="POST"> --}}
                                    {{-- @csrf
                                    @method('DELETE') --}}
                                    <button type="submit" class="remove_btn"><i class="fal fa-trash-alt"></i></button>
                                {{-- </form> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col col-lg-6">
                <div class="calculate_shipping">
                    <h3 class="wrap_title">Select One <span class="icon"><i class="far fa-arrow-up"></i></span></h3>
                    {{-- <form action="{{ route('shipping.calculate') }}" method="POST"> --}}
                        {{-- @csrf --}}
                        <div class="select_option clearfix">
                            <select name="shipping_option" required onchange="deliveryCharge(this.value)">
                                <option value="">Select Your Option</option>
                                <option value="40">Inside City (৳40)</option>
                                <option value="100">Outside City (৳100)</option>
                            </select>
                        </div>
                        <br>
                        {{-- <button type="submit" class="btn btn_primary rounded-pill">Calculate Shipping</button> --}}
                    {{-- </form> --}}
                </div>
            </div>

            <div class="col col-lg-6">
                <div class="cart_total_table">
                    <h3 class="wrap_title">Cart Totals</h3>
                    <ul class="ul_li_block">
                        <li>
                            <span>Cart Subtotal</span>
                            <span id="cart-subtotal">৳{{ number_format($subtotal, 2) }}</span>
                        </li>
                        <li>
                            <span>Delivery Charge</span>
                            <span id="delivery-charge">৳ 0</span>
                        </li>
                        <li>
                            <span>Discount</span>
                            <span id="discount">৳</span>
                        </li>
                        <li>
                            <span>Order Total</span>
                            <span class="total_price">৳{{ number_format($subtotal - (session('cart_discount')->amount ?? 0), 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
             <div class="cart_btns_wrap">
            <div class="row">
                <div class="col col-lg-6">
                    {{-- <form action="{{ route('coupon.apply') }}" method="POST">
                        @csrf --}}
                        <div class="coupon_form form_item mb-0">
                            <input type="text" name="coupon_code" class="txtcoupon" placeholder="Coupon Code..." required>
                            <button type="button" id="coupon-btn" class="btn btn-dark">Apply Coupon</button>
                            <div class="info_icon">
                                <i class="fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Enter your coupon code for discounts"></i>
                            </div>
                        </div>
                    {{-- </form> --}}
                </div>

                <div class="col col-lg-6">
                    <ul class="btns_group ul_li_right">
                        <li><a class="btn border_black" href="{{url('/')}}">Continue Shopping</a></li>
                        <form action="{{route('exampleHostedCheckout')}}" method="GET">
                            <input type="hidden" name="subtotal" value="{{ $subtotal }}" required>
                            <input type="hidden" id="total" name="total_price" value="{{ $subtotal - (session('cart_discount')->amount ?? 0) }}">
                            <input type="hidden" id="discount_price" name="discount" value="">
                            <input type="hidden" id="delivery" name="delivery_charge" value="">
                        <li><button type="submit" class="btn btn_dark" >Proceed To Checkout</button></li>
                        </form>
                    </ul>
                </div>
            </div>
        </div>
        @else
        <div class="empty_cart text-center">
            <i class="fal fa-shopping-cart fa-4x mb-4"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet</p>
            {{-- <a href="{{ route('shop') }}" class="btn btn_dark mt-3">Continue Shopping</a> --}}
        </div>
        @endif
    </div>

</section>

@push('scripts')
<script>
    function deliveryCharge(value){
        let charge = parseInt(value);
        let deliveryCharge = charge;
        let subtotal = parseInt($('#cart-subtotal').text().replace('৳', '').replace(',', ''));
        let total = subtotal + deliveryCharge;
        console.log(subtotal);
        
        $('#delivery-charge').text('৳' + deliveryCharge);
        $('#delivery').val(deliveryCharge);
        $('.total_price').text('৳' + total);
        $('#total').val(total);
    }
    $(document).ready(function () {
    
        // Increment button click
        $('[id^="input_number_increment-"]').on('click', function () {
            const id = $(this).data('id');
            const input = $('#input_number_2-' + id);
            let quantity = parseInt(input.val()) || 1;
            quantity++;
            input.val(quantity);
            updateCart(id, quantity);
        });

        // Decrement button click
        $('[id^="input_number_decrement-"]').on('click', function () {
            const id = $(this).data('id');
            const input = $('#input_number_2-' + id);
            let quantity = parseInt(input.val()) || 1;
            if (quantity > 1) {
                quantity--;
                input.val(quantity);
                updateCart(id, quantity);
            }
        });

        // On manual quantity change
        $('[id^="input_number_2-"]').on('change', function () {
            const id = $(this).data('id');
            let quantity = parseInt($(this).val()) || 1;
            if (quantity < 1) quantity = 1;
            $(this).val(quantity);
            updateCart(id, quantity);
        });

        function updateCart(id, quantity) {
            $.ajax({
                url: '{{ route("update.cart") }}', // Make sure this route exists
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    quantity: quantity
                },
                success: function (response) {
                    console.log(response);
                    
                     $('#item-total-' + id).text(response.itemTotal);
                    // Update subtotal
                    $('#cart-subtotal').text('৳' + response.subTotal);
                },
                error: function () {
                    alert('Cart update failed.');
                }
            });
        }
    });

    $('#coupon-btn').on('click',function(e){
        e.preventDefault();
        let couponCode =  $('.txtcoupon').val();
          if (!couponCode) {
            alert('Please enter a coupon code.');
            return;
        }

        $.ajax({
            url : '{{route("coupon.apply")}}',
            type: 'POST',
            data:{
                _token:'{{csrf_token()}}',
                coupon_code : couponCode
            },
            success:function(response){
                console.log(response);
                
                $('#discount').text('৳' + response.discount);
                $('#discount_price').val(response.discount);
                let deliveryChargeText = $('#delivery-charge').text(); // e.g., "৳50"
                let deliveryCharge = parseFloat(deliveryChargeText.replace('৳', '').trim());
                let newprice  =  response.total + deliveryCharge;
                $('.total_price').text('৳' + newprice);
                $('#total').val(newprice);
            }
        })
    });

    // Use Toastr for notifications. Configure basic options.
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    $(function(){
        // Session flash messages
        const sessMsg = $('#session-message').val();
        const sessErr = $('#session-error').val();
        if(sessMsg){
            toastr.success(sessMsg);
        }
        if(sessErr){
            toastr.error(sessErr);
        }

        // Query param notice fallback
        const noticeEl = $('#checkout-notice');
        if(noticeEl.length){
            const notice = noticeEl.val();
            if(notice === 'success'){
                toastr.success('Payment successful and order placed!');
            } else if(notice === 'failed' || notice === 'fail'){
                toastr.error('Payment failed or validation error. Please try again.');
            } else if(notice === 'cancel'){
                toastr.warning('Payment cancelled by user.');
            }
        }
    });
</script>
@endpush
</script>

@endsection