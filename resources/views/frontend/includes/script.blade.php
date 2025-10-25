 <!-- fraimwork - jquery include -->
 <script src="{{ asset('frontend') }}/assets/js/jquery.min.js"></script>
    <script src="{{ asset('frontend') }}/assets/js/popper.min.js"></script>
    <script src="{{ asset('frontend') }}/assets/js/bootstrap.min.js"></script>

    <!-- carousel - jquery plugins collection -->
    <script src="{{ asset('frontend') }}/assets/js/jquery-plugins-collection.js"></script>

    <!-- google map  -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDk2HrmqE4sWSei0XdKGbOMOHN3Mm2Bf-M&ver=2.1.6"></script>
    <script src="{{ asset('frontend') }}/assets/js/gmaps.min.js"></script>

    <!-- custom - main-js -->
    <script src="{{ asset('frontend') }}/assets/js/main.js"></script>

   <!-- Toastr JS for notifications -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-3MU6X6XKc1b1Q1c1Y8a2k1Z3p1Yk3Zs6d1F6Y2c1V6X1b3Y1k4c1V6X1b3Y1k4c1V6X1b3Y1k4c1V6X1b3Y==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

   {{-- Slot for page-specific scripts that must run after core JS (jQuery, Bootstrap, main.js) --}}
   @stack('scripts')
