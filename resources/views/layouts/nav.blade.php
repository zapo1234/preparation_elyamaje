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
  
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
            <li>
                <a href="{{ url('dashboard') }}">
                    <div class="parent-icon"><i class='bx bx-home'></i>
                    </div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
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
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-cog'></i>
                    </div>
                    <div class="menu-title">Configuration</div>
                </a>
                <ul>
                    <li> <a href="{{ url('configuration') }}"><i class="bx bx-right-arrow-alt"></i>Catégories</a>
                    </li>
                    <li> <a href="{{ url('account') }}"><i class="bx bx-right-arrow-alt"></i>Comptes</a>
                    </li>
                </ul>
            </li>

        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  2)) > 0)
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
                    <li> <a href="{{ url('ordersHistory') }}"><i class="bx bx-right-arrow-alt"></i>Historique</a>
                    </li>
                </ul>
            </li>
         @endif
         @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0)
            <li>
                <a href="{{ url('wrapOrder') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes</div>
                </a>
            </li>
        @endif
    </ul>
    
    <!--end navigation-->
</div>