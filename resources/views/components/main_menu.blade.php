<div class="header-center">
    <nav class="main-nav">
        <ul class="menu sf-arrows">
            <li class="megamenu-container  ">
                <a href="{{ route('home') }}"><strong>Trang chủ</strong></a>
            </li>
            {{-- @foreach ($categorysLimit as $categoryParent)
                <li>
                    <a href="" class="sf-with-ul">
                        {{ $categoryParent->name }}
                    </a>
                    @include('components.child_menu', ['categoryParent' => $categoryParent])
                </li>
            @endforeach --}}
            <li>
                <a href="{{ URL::to('/product_all') }}" class="sf-with-ul"><strong>Cửa hàng</strong></a>

                <div class="megamenu megamenu-md">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="menu-col">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="menu-title">Danh mục</div><!-- End .menu-title -->
                                        <ul>
                                            <li><a href="{{ URL::to('/product_all') }}">Áo Khoác</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Áo Sơ Mi</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Áo Thun</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Quần</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}"><span>Đầm, Váy<span
                                                            class="tip tip-new">New</span></span></a></li>
                                            <li><a href="{{ URL::to('/product_all') }}"><span>Đồ Lót<span></a></li>
                                            <li><a href="{{ URL::to('/product_all') }}"><span>Đồ Ngủ<span></a></li>
                                            <li><a href="{{ URL::to('/product_all') }}"><span>Phụ Kiện<span
                                                            class="tip tip-hot">Hot</span></span></a></li>
                                        </ul>
                                    </div><!-- End .col-md-6 -->
                                    <div class="col-md-6">
                                        <div class="menu-title">Thương hiệu</div><!-- End .menu-title -->
                                        <ul>
                                            <li><a href="{{ URL::to('/product_all') }}"><span>Louis Vuitton<span
                                                class="tip tip-hot">hot</span></span></a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Gucci</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Hermès</a></li>
                                            <li><a href="{{ URL::to('/product_all') }}">Loewe</a></li>
                                        </ul>
                                    </div><!-- End .col-md-6 -->
                                </div><!-- End .row -->
                            </div><!-- End .menu-col -->
                        </div><!-- End .col-md-8 -->

                        <div class="col-md-4">
                            <div class="banner banner-overlay">
                                <a href="{{ URL::to('/product_all') }}" class="banner banner-menu">
                                    <img src="{{ asset('UserLTE/assets/images/menu/banner-1.jpg') }}" alt="Banner">

                                    <div class="banner-content banner-content-top">
                                        <div class="banner-title text-white">Sản
                                            <br>Phẩm<br><span><strong>Hot</strong></span>
                                        </div>
                                        <!-- End .banner-title -->
                                    </div><!-- End .banner-content -->
                                </a>
                            </div><!-- End .banner banner-overlay -->
                        </div><!-- End .col-md-4 -->
                    </div><!-- End .row -->
                </div><!-- End .megamenu megamenu-md -->
            </li>
            <li>
                <a href="{{ URL::to('/about') }}" class=""><strong>Về chúng tôi</strong></a>
            </li>
            <li>
                <a href="{{ URL::to('/lien_he') }}" class=""><strong>Liên hệ</strong></a>
            </li>
        </ul><!-- End .menu -->
    </nav><!-- End .main-nav -->
</div><!-- End .header-center -->
