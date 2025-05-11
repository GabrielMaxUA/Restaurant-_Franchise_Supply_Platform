@extends('layouts.admin')

@section('title', 'Users - Restaurant Franchise Supply Platform')

@section('page-title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Users</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New User
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        {{ session('error') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search Users</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="mb-0">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="search_term" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search_term" name="search" 
                               placeholder="Name, email, or phone" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="company" class="form-label">Company/Franchise</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <input type="text" class="form-control" id="company" name="company" 
                               placeholder="Company name" value="{{ request('company') }}">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company/Franchise</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @if($user->franchiseeProfile)
                                    {{ $user->franchiseeProfile->company_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $user->role->name == 'admin' ? 'bg-danger' : ($user->role->name == 'warehouse' ? 'bg-primary' : 'bg-success') }}">
                                    {{ ucfirst($user->role->name) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->status ? 'Active' : 'Blocked' }}
                                </span>
                            </td>
                            <td>
                              <div class="d-flex action-buttons">
                                  {{-- View Button --}}
                                  <button type="button" class="btn btn-sm btn-info view-user-btn me-2" style="width: 36px" title="View" 
                                          data-user-id="{{ $user->id }}">
                                      <i class="fas fa-eye"></i>
                                  </button>

                                  {{-- Edit Button --}}
                                  <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning me-2" style="width: 36px" title="Edit">
                                      <i class="fas fa-edit"></i>
                                  </a>

                                  {{-- Delete Button - only shown if not the current user --}}
                                  @if($user->id !== auth()->id())
                                      <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn btn-sm btn-danger" style="width: 36px"
                                                  title="Delete"
                                                  onclick="return confirm('Are you sure you want to delete this user?')">
                                              <i class="fas fa-trash"></i>
                                          </button>
                                      </form>
                                  @endif
                              </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>


{{-- Include the user info modal component --}}
@include('layouts.components.user-info-modal')
         
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Highlight search results if search term exists
        const searchTerm = "{{ request('search') }}";
        if (searchTerm.length > 0) {
            const regex = new RegExp(searchTerm, 'gi');
            $('tbody td:not(:last-child)').each(function() {
                const text = $(this).text();
                if (text.match(regex)) {
                    const highlightedText = text.replace(regex, match => `<mark>${match}</mark>`);
                    $(this).html(highlightedText);
                }
            });
        }

        // Enable tooltip
        $('[title]').tooltip();
    });
</script>
@endsection