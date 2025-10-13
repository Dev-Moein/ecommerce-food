@extends('layout.master')
@section('title', 'Menu Page')

@section('script')
    <script type="text/javascript">
        document.addEventListener('alpine:init', () => {
            Alpine.data('filter', () => ({
                search: '',
                currentUrl: '{{ url()->current() }}',
                params: new URLSearchParams(location.search),

                filter(type, value) {
                    this.params.set(type, value);
                    this.params.delete('page');
                    document.location.href = this.currentUrl + '?' + this.params.toString();
                },
                removeFilter(type) {
                    this.params.delete(type);
                    this.params.delete('page');
                    document.location.href = this.currentUrl + '?' + this.params.toString();
                }
            }))
        });
    </script>
@endsection

@section('content')
    <section class="food_section layout_padding">
        <div class="container">
            <div class="row">
                <div x-data="filter" class="col-sm-12 col-lg-3">
                    <div>
                        <label class="form-label">جستجو
                            @if (request()->has('search'))
                                <i @click="removeFilter('search')" class="bi bi-x text-danger fs-5 cursor-pointer"></i>
                            @endif
                        </label>

                        <div class="input-group mb-3">
                            <input type="text" x-model="search" class="form-control" placeholder="نام محصول ..." />
                            <button @click="filter('search', search)" class="input-group-text">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <hr />
                    <div class="filter-list">
                        <div class="form-label">
                            دسته بندی
                             @if (request()->has('category'))
                                <i @click="removeFilter('category')" class="bi bi-x text-danger fs-5 cursor-pointer"></i>
                            @endif
                        </div>
                        <ul>
                            @foreach ($categories as $category)
                                <li @click="filter('category', '{{$category->id}}')"
                                class="my-2 cursor-pointer {{request()->has('category')&& request()->category == $category->id ? 'filter-list-active' : '' }}">{{$category->name}}</li>
                            @endforeach
                        </ul>
                    </div>
                    <hr />
                    <div>
                        <label class="form-label">مرتب سازی
                               @if (request()->has('sortBy'))
                              <i @click="removeFilter('sortBy')" class="bi bi-x text-danger fs-5 cursor-pointer"></i>
                              @endif
                        </label>
                        <div class="form-check my-2">
                            <input {{request()->has('sortBy')&& request()->sortBy == 'max' ? 'checked' : ''}}
                            @change="filter('sortBy','max')" class="form-check-input" type="radio" name="flexRadioDefault" />
                            <label class="form-check-label cursor-pointer">
                                بیشترین قیمت
                            </label>
                        </div>
                        <div class="form-check my-2">
                            <input  {{request()->has('sortBy')&& request()->sortBy == 'min' ? 'checked' : ''}}
                              @change="filter('sortBy','min')" class="form-check-input" type="radio" name="flexRadioDefault"  />
                            <label class="form-check-label cursor-pointer">
                                کمترین قیمت
                            </label>
                        </div>
                        <div class="form-check my-2">
                            <input  {{request()->has('sortBy')&& request()->sortBy == 'bestseller' ? 'checked' : ''}}
                             @change="filter('sortBy','bestseller')" class="form-check-input" type="radio" name="flexRadioDefault" />
                            <label class="form-check-label cursor-pointer">
                                پرفروش ترین
                            </label>
                        </div>
                        <div class="form-check my-2">
                            <input {{request()->has('sortBy')&& request()->sortBy == 'sale' ? 'checked' : ''}}
                              @change="filter('sortBy','sale') "class="form-check-input" type="radio" name="flexRadioDefault" />
                            <label class="form-check-label cursor-pointer">
                                با تخفیف
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-9">
                    @if ($products->isEmpty())
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <h5>محصولی یافت نشده!</h5>
                        </div>
                    @endif

                    <div class="row gx-3">
                        @foreach ($products as $product)
                            <div class="col-sm-6 col-lg-4">
                                <div class="box">
                                    <div>
                                        <div class="img-box">
                                            <img class="img-fluid" src="{{ imageUrl($product->primary_image) }}"
                                                alt="">
                                        </div>
                                        <div class="detail-box">
                                            <h5>
                                                <a href="{{ route('product.show', ['product' => $product->slug]) }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h5>
                                            <p>
                                                {{ $product->description }}
                                            </p>

                                            <div class="options">
                                                @if ($product->is_sale)
                                                    <h6>
                                                        <del>{{ number_format($product->price) }}</del>
                                                        <span>
                                                            <span
                                                                class="text-danger">({{ salePercent($product->price, $product->sale_price) }}%)</span>
                                                            {{ number_format($product->sale_price) }}
                                                            <span>تومان</span>
                                                        </span>
                                                    </h6>
                                                @else
                                                    <h6>
                                                        {{ number_format($product->price) }}
                                                        <span>تومان</span>
                                                    </h6>
                                                @endif

                                                <div class="d-flex">
                                                    <a class="me-2" href="">
                                                        <i class="bi bi-cart-fill text-white fs-6"></i>
                                                    </a>
                                                    <a href="">
                                                        <i class="bi bi-heart-fill text-white fs-6"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{ $products->withQueryString()->links('layout.paginate') }}
                </div>
            </div>
        </div>
    </section>
@endsection
