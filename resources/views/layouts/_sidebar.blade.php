<nav class="page-sidebar" id="sidebar">
    <div id="sidebar-collapse">
        <ul class="side-menu metismenu">
            <li class="">
                <a href="{{ route('dashboard') }}"><i class="sidebar-item-icon ti-home"></i>
                    <span class="nav-label">Dashboard </span>
                </a>
            </li>
            @if (Auth::user()->roles == 'manager')
                <li class="heading">Manager</li>
                <li>
                    <a href="javascript:;"><i class="sidebar-item-icon ti-money"></i>
                        <span class="nav-label"> Transaction </span></a>
                </li>
            @elseif (Auth::user()->roles == 'admin')
                <li class="heading">Admin</li>
                <li>
                    <a href="{{ route('menu.list') }}"><i class="sidebar-item-icon ti-shopping-cart"></i><span class="nav-label"> Menu</span></a>
                </li>
                <li>
                    <a href="{{ route('user.list') }}"><i class="sidebar-item-icon ti-user"></i><span class="nav-label"> User</span> </a>
                </li>
                <li>
                    <a href=""><i class="sidebar-item-icon ti-tablet"></i><span class="nav-label"> Table</span></a>
                </li>
            @endif
            
        </ul>
    </div>
</nav>