@extends('layouts.admin')

@section('title', isset($username) ? "Orders for {$username} - Restaurant Franchise Supply Platform" : 'Orders - Restaurant Franchise Supply Platform')
@section('page-title', $pageTitle ?? 'Order Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    .pagination-wrapper {
        padding: 1rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        text-align: center;
    }
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
    .pagination {
        display: inline-flex;
        list-style: none;
        padding-left: 0;
        margin: 0;
        border-radius: 0.375rem;
        justify-content: center;
    }
    .page-item {
        display: inline-block;
    }
    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: #4e73df;
        background-color: #fff;
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
        min-width: 40px;
        text-align: center;
    }
    .page-item.active .page-link {
        background-color: #4e73df;
        color: #fff;
        border-color: #4e73df;
    }
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $pageTitle ?? 'Manage Orders' }}</h1>
    @if(isset($username))
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> View All Orders
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="data-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Invoice #</th>
                    <th>Franchisee</th>
                    <th>Company</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th class="text-center">Status</th>
                    <th>QuickBooks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>
                        @if($order->status == 'pending')
                            <span class="text-muted"><i class="fas fa-clock"></i> Pending</span>
                        @elseif($order->invoice_number)
                            <span class="text-primary">{{ $order->invoice_number }}</span>
                        @else
                            <span class="text-muted">Not generated</span>
                        @endif
                    </td>
                    <td>{{ $order->user->username ?? 'Unknown' }}</td>
                    <td>{{ $order->user->franchiseeProfile->company_name ?? 'Unknown' }}</td>
                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td class="text-center">
                        @php
                            $badge = match($order->status) {
                                'pending' => 'bg-warning text-dark',
                                'approved' => 'bg-info',
                                'packed' => 'bg-secondary',
                                'shipped' => 'bg-primary',
                                'delivered' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        @if($order->qb_invoice_id)
                            <span class="text-success"><i class="fas fa-check-circle"></i> {{ $order->qb_invoice_id }}</span>
                        @else
                            <span class="text-muted">Not Synced</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
            </div>
            <nav>
                <ul class="pagination justify-content-center">
                    {{-- Previous --}}
                    @if ($orders->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-left"></i></span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $orders->previousPageUrl() }}" rel="prev">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pages --}}
                    @foreach ($orders->links()->elements[0] as $page => $url)
                        @if ($page == $orders->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($orders->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $orders->nextPageUrl() }}" rel="next">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-right"></i></span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @else
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $orders->count() }} result{{ $orders->count() !== 1 ? 's' : '' }}
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('.date-picker', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });
    });
</script>
@endsection
