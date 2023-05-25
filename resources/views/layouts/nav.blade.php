<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="assets/images/Logo_elyamaje.png" class="logo-icon" alt="logo icon">
        </div>

        <div class="toggle-icon ms-auto"><i class='bx bx-first-page'></i>
        </div>
    </div>

    <!--navigation-->

    <ul class="metismenu" id="menu">
        @if(Auth()->user()->role_id == 1)
            <li>
                <a href="{{ url('index') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes</div>
                </a>
            </li>
            <li>
                <a href="{{ url('refreshtiers') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Import Api tiers</div>
                </a>
            </li>
            <li>
                <a href="{{ url('configuration') }}">
                    <div class="parent-icon"><i class='bx bx-cog'></i>
                    </div>
                    <div class="menu-title">Configuration</div>
                </a>
            </li>
        @elseif(Auth()->user()->role_id == 2)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes</div>
                </a>
                <ul>
                    <li> <a href="{{ url('orders') }}"><i class="bx bx-right-arrow-alt"></i>Internet</a>
                    </li>
                    <li> <a href="{{ url('ordersDistributeurs') }}"><i class="bx bx-right-arrow-alt"></i>Distributeurs</a>
                    </li>
                </ul>
            </li>
        @elseif(Auth()->user()->role_id == 3)
            <li>
                <a href="{{ url('wrapOrder') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes</div>
                </a>
            </li>
        @elseif(Auth()->user()->role_id == 4)
            <li>
                <a href="{{ url('dashboard') }}">
                    <div class="parent-icon"><i class='bx bx-home'></i>
                    </div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
        @endif


    </ul>
    
    <!--end navigation-->
</div>