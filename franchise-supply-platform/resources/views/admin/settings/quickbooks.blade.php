@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">QuickBooks Integration</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">QuickBooks Connection Status</h6>
                </div>
                <div class="card-body">
                    @if(!$isConfigured)
                        <div class="alert alert-warning">
                            <strong>Configuration Required</strong><br>
                            QuickBooks API credentials are not properly configured. Please add the following environment variables:
                            <ul>
                                <li>QB_CLIENT_ID</li>
                                <li>QB_CLIENT_SECRET</li>
                                <li>QB_REDIRECT_URI</li>
                                <li>QB_INTEGRATION_ENABLED</li>
                            </ul>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="font-weight-bold">Connection Status</h5>
                                    @if($isConnected)
                                        <div class="mb-2">
                                            <span class="badge badge-success">Connected</span>
                                        </div>
                                        <p>
                                            <strong>Company ID:</strong> {{ $realmId }}<br>
                                            <strong>Token Expires:</strong> {{ $tokenExpiry }}
                                        </p>
                                        
                                        <div class="mt-3">
                                            <a href="#" class="btn btn-sm btn-info" id="test-connection">
                                                <i class="fas fa-sync"></i> Test Connection
                                            </a>
                                            <a href="{{ route('admin.quickbooks.disconnect') }}" class="btn btn-sm btn-danger">
                                                <i class="fas fa-unlink"></i> Disconnect
                                            </a>
                                            <a href="#" class="btn btn-sm btn-warning" id="refresh-token">
                                                <i class="fas fa-redo"></i> Refresh Token
                                            </a>
                                        </div>
                                        
                                        <div class="mt-3" id="connection-test-result" style="display: none;">
                                        </div>
                                    @else
                                        <div class="mb-2">
                                            <span class="badge badge-danger">Disconnected</span>
                                        </div>
                                        <p>Your QuickBooks account is not connected.</p>
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('admin.quickbooks.connect') }}" class="btn btn-primary">
                                                <i class="fas fa-link"></i> Connect to QuickBooks
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="font-weight-bold">Integration Settings</h5>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="enable-integration" 
                                                {{ config('services.quickbooks.integration_enabled') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="enable-integration">
                                                Enable QuickBooks Integration
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            When disabled, the system will use mock data instead of making actual API calls.
                                        </small>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold">Integration Options</h6>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="sync-customers" checked>
                                                <label class="custom-control-label" for="sync-customers">
                                                    Sync franchisees as customers
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="sync-orders" checked>
                                                <label class="custom-control-label" for="sync-orders">
                                                    Sync orders as invoices
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">Recent QuickBooks Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center">No recent activities</td>
                                </tr>
                                <!-- In a real implementation, you would populate this with actual activities -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Test connection button
    $("#test-connection").click(function(e) {
        e.preventDefault();
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        $.ajax({
            url: '{{ route("admin.quickbooks.test-connection") }}',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $("#test-connection").html('<i class="fas fa-sync"></i> Test Connection');
                
                let resultHtml = '';
                if (response.success) {
                    resultHtml = `
                        <div class="alert alert-success">
                            <strong>Connection Successful!</strong><br>
                            Connected to: ${response.company_name}
                        </div>
                    `;
                } else {
                    resultHtml = `
                        <div class="alert alert-danger">
                            <strong>Connection Failed</strong><br>
                            ${response.message}
                        </div>
                    `;
                }
                
                $("#connection-test-result").html(resultHtml).show();
            },
            error: function(xhr) {
                $("#test-connection").html('<i class="fas fa-sync"></i> Test Connection');
                
                let errorMessage = 'An error occurred while testing the connection.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $("#connection-test-result").html(`
                    <div class="alert alert-danger">
                        <strong>Connection Failed</strong><br>
                        ${errorMessage}
                    </div>
                `).show();
            }
        });
    });
    
    // Refresh token button
    $("#refresh-token").click(function(e) {
        e.preventDefault();
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        
        $.ajax({
            url: '{{ route("admin.quickbooks.refresh-token") }}',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $("#refresh-token").html('<i class="fas fa-redo"></i> Refresh Token');
                
                if (response.success) {
                    $("#connection-test-result").html(`
                        <div class="alert alert-success">
                            <strong>Token Refreshed Successfully</strong><br>
                            New expiration: ${response.expires_at}
                        </div>
                    `).show();
                    
                    // Update the displayed expiry date
                    window.location.reload();
                } else {
                    $("#connection-test-result").html(`
                        <div class="alert alert-danger">
                            <strong>Token Refresh Failed</strong><br>
                            ${response.message}
                        </div>
                    `).show();
                }
            },
            error: function(xhr) {
                $("#refresh-token").html('<i class="fas fa-redo"></i> Refresh Token');
                
                let errorMessage = 'An error occurred while refreshing the token.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $("#connection-test-result").html(`
                    <div class="alert alert-danger">
                        <strong>Token Refresh Failed</strong><br>
                        ${errorMessage}
                    </div>
                `).show();
            }
        });
    });
    
    // Enable/disable integration toggle
    $("#enable-integration").change(function() {
        const isEnabled = $(this).prop('checked');
        
        // In a real implementation, you would make an AJAX call to update the setting
        // For demo purposes, we'll just show a confirmation message
        
        let message = isEnabled ? 
            'QuickBooks integration has been enabled. The system will now make live API calls.' : 
            'QuickBooks integration has been disabled. The system will use mock data instead of making API calls.';
        
        alert(message);
    });
});
</script>
@endpush