
<!doctype html>
<html lang="en">
<head>
    @include('frontend.includes.head')
    @include('frontend.includes.css')
    </head>

<body>

    <!-- body_wrap - start -->
    <div class="body_wrap">
        
        <!-- backtotop - start -->
        <div class="backtotop">
            <a href="#" class="scroll">
                <i class="far fa-arrow-up"></i>
            </a>
        </div>
        <!-- backtotop - end -->

        <!-- preloader - start -->
        <div id="preloader"></div>
        <!-- preloader - end -->

        
        <!-- header_section - start
        ================================================== -->
        <header class="header_section header-style-no-collapse"> 
             @include('frontend.includes.header_top')          
            @include('frontend.includes.topbar')
        </header>

        <!-- header_section - end
        ================================================== -->
        
        <!-- main body - start
        ================================================== -->
        <main>
            <!-- sidebar cart - start
            ================================================== -->
            @include('frontend.includes.sidebar')
            <!-- sidebar cart - end
            ================================================== -->

            <!-- product quick view modal - start
            ================================================== -->
            @include('frontend.includes.productquickview')
            <!-- product quick view modal - end
            ================================================== -->

            <!-- slider_section - start
            ================================================== -->
            @include('frontend.includes.slider')
            <!-- slider_section - end
            ================================================== -->

            <!-- policy_section - start
            ================================================== -->
           
            <!-- policy_section - end
            ================================================== -->

            <!-- products-with-sidebar-section - start
            ================================================== -->
            @include('frontend.includes.bestselling')
            <!-- products-with-sidebar-section - end
            ================================================== -->

            <!-- promotion_section - start
            ================================================== -->
            @include('frontend.includes.promotion')
            <!-- promotion_section - end
            ================================================== -->

            <!-- new_arrivals_section - start
            ================================================== -->
            @include('frontend.includes.newarivals')
            <!-- new_arrivals_section - end
            ================================================== -->

            <!-- brand_section - start
            ================================================== -->
            @include('frontend.includes.brand')
            <!-- brand_section - end
            ================================================== -->

            <!-- viewed_products_section - start
            ================================================== -->
            @include('frontend.includes.recentview')
            <!-- viewed_products_section - end
            ================================================== -->

            <!-- newsletter_section - start
            ================================================== -->
            @include('frontend.includes.subscribe')
            <!-- newsletter_section - end
            ================================================== -->
        </main>
        <!-- main body - end
        ================================================== -->
        
        <!-- footer_section - start
        ================================================== -->
        @include('frontend.includes.footer')
        <!-- footer_section - end
        ================================================== -->

    </div>
    <!-- body_wrap - end -->
    @include('frontend.includes.script')
   
</body>
</html>