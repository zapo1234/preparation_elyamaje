<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div class="d-flex flex-column">
            <img src="assets{{ ('/images/elyamaje_logo_long_noir.png') }}" class="logo-icon" alt="logo icon">
            <img src="assets{{ ('/images/Logo_elyamaje.png') }}" class="d-none logo-icon-2" alt="logo icon">

            <!-- <span class="header_name_logo">Préparation</span> -->
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
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-history'></i>
                    </div>
                    <div class="menu-title">Historiques</div>
                </a>
                <ul>
                    <li> <a href="{{ url('leaderHistory') }}"><i class="bx bx-right-arrow-alt"></i>Commandes</a>
                    </li>
                    <li> <a href="{{ url('leaderHistoryOrder') }}"><i class="bx bx-right-arrow-alt"></i>Préparation</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-cog'></i>
                    </div>
                    <div class="menu-title">Configuration</div>
                </a>
                <ul>
                    <li> <a href="{{ url('account') }}"><i class="bx bx-right-arrow-alt"></i>Comptes</a>
                    </li>
                    <li> <a href="{{ url('printers') }}"><i class="bx bx-right-arrow-alt"></i>Imprimantes</a>
                    </li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
            <li>
                <a href="{{ url('analytics') }}">
                    <div class="parent-icon"><i class='bx bx-data'></i>
                    </div>
                    <div class="menu-title">Analytics</div>
                </a>
            </li>    
            <li>
                <a href="{{ url('indexAdmin') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes</div>
                </a>
            </li>
            <li>
                <a href="{{ url('billing') }}">
                    <div class="parent-icon"><i class='bx bx-credit-card-front'></i>
                    </div>
                    <div class="menu-title">Facturation</div>
                </a>
            </li>
            <li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-history'></i>
                    </div>
                    <div class="menu-title">Historiques</div>
                </a>
                <ul>
                    <li> <a href="{{ url('leaderHistory') }}"><i class="bx bx-right-arrow-alt"></i>Commandes</a>
                    </li>
                    <li> <a href="{{ url('leaderHistoryOrder') }}"><i class="bx bx-right-arrow-alt"></i>Préparation</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ url('orderfacturer') }}">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Commandes facturés</div>
                </a>
            </li>
            <li>
                <a href="{{ url('refreshtiers') }}">
                    <div class="parent-icon"><i class="bx bx-lock-open"></i>
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
                    <li> <a href="{{ url('categories') }}"><i class="bx bx-right-arrow-alt"></i>Catégories</a>
                    </li>
                    <li> <a href="{{ url('products') }}"><i class="bx bx-right-arrow-alt"></i>Produits</a>
                    </li>
                    <li> <a href="{{ url('account') }}"><i class="bx bx-right-arrow-alt"></i>Comptes</a>
                    </li>
                    <li> <a href="{{ url('roles') }}"><i class="bx bx-right-arrow-alt"></i>Roles</a>
                    </li>
                    <li> <a href="{{ url('distributors') }}"><i class="bx bx-right-arrow-alt"></i>Distributeurs</a>
                    </li>
                    <li> <a href="{{ url('printers') }}"><i class="bx bx-right-arrow-alt"></i>Imprimantes</a>
                    </li>
                    <li> <a href="{{ url('colissimo') }}"><i class="bx bx-right-arrow-alt"></i>Colissimo</a>
                    </li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  2)) > 0)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-box'></i>
                    </div>
                    <div class="menu-title">Préparation</div>
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
                    <div class="menu-title">Emballer</div>
                </a>
            </li>
        @endif

        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="lni lni-delivery"></i>
                    </div>
                    <div class="menu-title">Colissimo</div>
                </a>
                <ul>
                    <li> <a href="{{ url('labels') }}"><i class="bx bx-right-arrow-alt"></i>Étiquettes</a>
                    </li>
                    <li> <a href="{{ url('bordereaux') }}"><i class="bx bx-right-arrow-alt"></i>Borderaux</a>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
    
    <!--end navigation-->
</div>