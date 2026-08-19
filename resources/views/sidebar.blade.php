<div class="sidebar d-none d-md-flex flex-column shadow">

    <div class="sidebar-header">
        <h4 class="mb-0">
            Dashboard
        </h4>
    </div>

    <ul class="nav nav-pills flex-column gap-2 px-3">

        <li class="nav-item">
            <a href="{{ route('allUsers') }}" class="sidebar-link nav-link ">

                <span>All Users</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('addUser') }}" class="sidebar-link nav-link  ">

                <span>Add User</span>
            </a>
        </li>

    </ul>

    <div class="sidebar-footer mt-auto p-3">

        <hr>

        <div class="small text-muted mb-1">
            Logged in as
        </div>

        <div class="user-name">
            <i class="bi bi-person-circle me-2"></i>
            {{ auth()->user()->name ?? 'Guest' }}
        </div>

    </div>

</div>

<style>
    .sidebar {
        width: 250px;
        height: calc(100vh - 64px);
        position: fixed;
        top: 64px;
        left: 0;
        background-color: white;
        z-index: 1050;
        border-right: 1px solid #e9ecef;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 25px 25px 20px;
        color: #212529;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 15px;
    }

    .sidebar-link {
        color: #ffffff !important;
        padding: 12px 15px !important;
        background-color: #3f8cff;
        display: flex !important;
        align-items: center;
        gap: 12px;
        transition: 0.3s;
        font-weight: 500;
    }

    .sidebar-link i {
        font-size: 18px;
    }

    .sidebar-link:hover {
        background-color: rgb(1, 120, 238);
        /* color: #262b34 !important; */
    }

    .sidebar-link.active {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .sidebar-footer {
        border-top: 1px solid #e9ecef;
    }

    .user-name {
        color: #212529;
        font-weight: 600;
    }
</style>
