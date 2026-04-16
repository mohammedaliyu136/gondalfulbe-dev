@extends('layouts.admin')
@section('page-title')
    {{__('Manage Purchase')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Purchase')}}</li>
@endsection
@push('script-page')
    <script>
        $(document).ready(function() {
            $('.copy_link').click(function (e) {
                e.preventDefault();
                var copyText = $(this).attr('href');

                document.addEventListener('copy', function (e) {
                    e.clipboardData.setData('text/plain', copyText);
                    e.preventDefault();
                }, true);

                document.execCommand('copy');
                show_toastr('success', 'Url copied to clipboard', 'success');
            });
            
            // Function to handle per_page changes
            function updatePerPage(value) {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', value);
                // Reset to first page when changing per_page
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
            
            // Make the function global
            window.updatePerPage = updatePerPage;
            
            // Clear search functionality
            $('#clear-search').click(function() {
                $('input[name="search"]').val('');
                window.location.href = "{{ route('purchase.index') }}?per_page={{ request('per_page', 20) }}";
            });
        });
    </script>
@endpush


@section('action-btn')
    <div class="float-end">
        @can('create purchase')
            <a href="{{ route('purchase.create',0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <div class="row mb-4">
    <div class="col-12 col-lg-6 mb-3 mb-lg-0">
        <div class="d-flex align-items-center bg-light p-3 rounded">
            <label for="per_page" class="form-label mb-0 me-3 fw-medium">{{ __('Per Page:') }}</label>
            <select name="per_page" id="per_page" class="form-select shadow-sm" onchange="window.updatePerPage(this.value)" style="width: 70px;">
                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <!-- Keep search parameter when submitting -->
            @if(request()->has('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <form method="GET" action="{{ route('purchase.index') }}" class="bg-light p-3 rounded">
            <div class="input-group">
                <input type="text" name="search" class="form-control shadow-sm" placeholder="Search purchases..." value="{{ request('search', '') }}">
                <!-- Keep per_page parameter when searching -->
                @if(request()->has('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="ti ti-search me-1"></i> Search
                </button>
                @if(request()->has('search') && !empty(request('search')))
                    <a href="{{ route('purchase.index') }}?per_page={{ request('per_page', 20) }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="ti ti-x me-1"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>



                        @if(request()->has('search') && !empty(request('search')))
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-info-circle"></i> 
                                Showing results for search: "<strong>{{ request('search') }}</strong>"
                                <a href="{{ route('purchase.index') }}?per_page={{ request('per_page', 20) }}" class="float-end">Clear search</a>
                            </div>
                        @endif

                        <table class="table datatable-">
                            <thead>
                                <tr>
                                    <th> {{__('Purchase')}}</th>
                                    <th> {{__('Vendor')}}</th>
                                    <th> {{__('Category')}}</th>
                                    <th> {{__('Purchase Date')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Creator')}}</th>
                                    @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                        <th>{{__('Action')}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($purchases) > 0)
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td class="Id">
                                                <a href="{{ route('purchase.show',\Crypt::encrypt($purchase->id)) }}" 
                                                   class="btn btn-outline-primary">
                                                   {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}
                                                </a>
                                            </td>
                                            <td> {{ $purchase->vender->name ?? '' }} </td>
                                            <td>{{ $purchase->category->name ?? '' }}</td>
                                            <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                                            <td>
                                                <span class="purchase_status badge 
                                                    {{ $purchase->status == 0 ? 'bg-secondary' : '' }}
                                                    {{ $purchase->status == 1 ? 'bg-warning' : '' }}
                                                    {{ $purchase->status == 2 ? 'bg-danger' : '' }}
                                                    {{ $purchase->status == 3 ? 'bg-info' : '' }}
                                                    {{ $purchase->status == 4 ? 'bg-primary' : '' }}
                                                    p-2 px-3 rounded">
                                                    {{ __(\App\Models\Purchase::$statues[$purchase->status]) }}
                                                </span>
                                            </td>
                                            <td>{{ \App\Models\User::find($purchase->creator)->name ?? '' }}</td>
                                            
                                            @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                                <td class="Action">
                                                    <span>
                                                        @can('show purchase')
                                                            <div class="action-btn me-2">
                                                                <a href="{{ route('purchase.show',\Crypt::encrypt($purchase->id)) }}" 
                                                                   class="mx-3 btn btn-sm bg-warning" 
                                                                   data-bs-toggle="tooltip" 
                                                                   title="{{__('Show')}}">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit purchase')
                                                            @if($purchase->status == 1)
                                                                <div class="action-btn me-2">
                                                                    <a href="{{ route('purchase.edit',\Crypt::encrypt($purchase->id)) }}" 
                                                                       class="mx-3 btn btn-sm bg-info" 
                                                                       data-bs-toggle="tooltip" 
                                                                       title="Edit">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                        @can('delete purchase')
                                                            @if($purchase->status == 0)
                                                                <div class="action-btn">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['purchase.destroy', $purchase->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$purchase->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm bg-danger bs-pass-para" 
                                                                       data-bs-toggle="tooltip" 
                                                                       title="{{__('Delete')}}" 
                                                                       data-confirm="{{__('Are You Sure?').'|'.__('This action cannot be undone.')}}" 
                                                                       data-confirm-yes="document.getElementById('delete-form-{{$purchase->id}}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endif
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            @if(request()->has('search') && !empty(request('search')))
                                                No purchases found matching your search criteria.
                                            @else
                                                No purchases found.
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="mt-3 d-flex justify-content-left">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    {{-- First Page Link --}}
                                    @if (!$purchases->onFirstPage())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $purchases->url(1) . '&' . http_build_query(request()->except('page', 'per_page')) . '&per_page=' . request('per_page', 20) }}" rel="first">&laquo;&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Previous Page Link --}}
                                    @if ($purchases->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $purchases->previousPageUrl() . '&' . http_build_query(request()->except('page', 'per_page')) . '&per_page=' . request('per_page', 20) }}" rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $totalPages = $purchases->lastPage();
                                        $currentPage = $purchases->currentPage();
                                        // Show 10 pages around the current page (5 on each side)
                                        $start = max(1, $currentPage - 5);
                                        $end = min($totalPages, $currentPage + 4);
                                        
                                        // Adjust if we're near the beginning
                                        if ($currentPage <= 5) {
                                            $end = min($totalPages, 10);
                                        }
                                        
                                        // Adjust if we're near the end
                                        if ($currentPage > $totalPages - 5) {
                                            $start = max(1, $totalPages - 9);
                                        }
                                    @endphp

                                    @for ($page = $start; $page <= $end; $page++)
                                        <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $purchases->url($page) . '&' . http_build_query(request()->except('page', 'per_page')) . '&per_page=' . request('per_page', 20) }}">{{ $page }}</a>
                                        </li>
                                    @endfor

                                    {{-- Next Page Link --}}
                                    @if ($purchases->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $purchases->nextPageUrl() . '&' . http_build_query(request()->except('page', 'per_page')) . '&per_page=' . request('per_page', 20) }}" rel="next">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">&raquo;</span>
                                        </li>
                                    @endif

                                    {{-- Last Page Link --}}
                                    @if ($purchases->currentPage() < $totalPages)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $purchases->url($totalPages) . '&' . http_build_query(request()->except('page', 'per_page')) . '&per_page=' . request('per_page', 20) }}" rel="last">&raquo;&raquo;</a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection