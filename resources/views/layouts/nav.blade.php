<div class="sidebar close">
    <div class="logo-details">
      <!-- <i class='bx bxl-c-plus-plus'></i> -->
      <!-- <span class="logo_name">CodingLab</span> -->
        <div class="div_short_icon">
            <img src="assets{{ ('/images/short_logo_blanc.png') }}" class="logo-short" alt="logo icon">
        </div>
        <img src="assets{{ ('/images/elyamaje_logo_long_blanc.png') }}" class="logo_name logo-icon" alt="logo icon">
    </div>
    <ul class="nav-links">
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
            <li>
                <a href="{{ url('dashboard') }}">
                    <i class='bx bx-grid-alt' ></i>
                    <span class="link_name">Dashboard</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('dashboard') }}">Dashboard</a></li>
                </ul>
            </li>
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='bx bx-history' ></i>
                    <span class="link_name">Historiques</span>
                </a>
                <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Historiques</a></li>
                    <li><a href="{{ url('leaderHistoryOrder') }}">Préparées</a></li>
                    <li><a href="{{ url('leaderHistory') }}">Commandes</a></li>
                </ul>
            </li>
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='bx bx-cog'></i>
                    <span class="link_name">Configuration</span>
                </a>
                <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Configuration</a></li>
                    <li><a href="{{ url('account') }}">Comptes</a></li>
                    <li><a href="{{ url('printers') }}">Imprimantes</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ url('getVieuxSplay') }}">
                    <i class='bx bx-transfer'></i>
                    <span class="link_name">Transferts</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('getVieuxSplay') }}">Transferts</a></li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
            <li>
                <a href="{{ url('indexAdmin') }}">
                    <i class='bx bx-grid-alt'></i>
                    <span class="link_name">Dashboard</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('indexAdmin') }}">Dashboard</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ url('analytics') }}">
                    <i class='bx bx-line-chart-down'></i>
                    <span class="link_name">Analytics</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('analytics') }}">Analytics</a></li>
                </ul>
            </li>
            <li>
                <div class="iocn-link">
                    <a href="#">
                        <i class='bx bx-credit-card-front'></i>
                        <span class="link_name">Facturation</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Facturation</a></li>
                    <li><a href="{{ url('billing') }}">Facturer</a></li>
                    <li><a href="{{ url('reinvoice') }}">Refacturer</a></li>
                </ul>
            </li>
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='bx bx-history' ></i>
                    <span class="link_name">Historiques</span>
                </a>
                <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Historiques</a></li>
                    <li><a href="{{ url('leaderHistoryOrder') }}">Préparées</a></li>
                    <li><a href="{{ url('leaderHistory') }}">Commandes</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ url('orderfacturer') }}">
                    <i class='bx bx-box'></i>
                    <span class="link_name">Dolibarr</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('orderfacturer') }}">Dolibarr</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ url('refreshtiers') }}">
                    <i class='bx bx-import'></i>
                    <span class="link_name">Import clients</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('refreshtiers') }}">Import clients</a></li>
                </ul>
            </li>
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='bx bx-cog'></i>
                    <span class="link_name">Configuration</span>
                </a>
                <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Configuration</a></li>
                    <li><a href="{{ url('categories') }}">Catégories</a></li>
                    <li><a href="{{ url('products') }}">Produits</a></li>
                    <li><a href="{{ url('account') }}">Comptes</a></li>
                    <li><a href="{{ url('roles') }}">Roles</a></li>
                    <li><a href="{{ url('distributors') }}">Distributeurs</a></li>
                    <li><a href="{{ url('printers') }}">Imprimantes</a></li>
                    <li><a href="{{ url('colissimo') }}">Colissimo</a></li>
                    <li><a href="{{ url('configDolibarr') }}">Dolibarr</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ url('getVieuxSplay') }}">
                    <i class='bx bx-transfer'></i>
                    <span class="link_name">Transferts</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('getVieuxSplay') }}">Transferts</a></li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  2)) > 0)
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='bx bx-box'></i>
                    <span class="link_name">Préparation</span>
                </a>
                <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Préparation</a></li>
                    <li class="orders_customer"><a href="{{ url('orders') }}">Internet</a></li>
                    <li class="orders_distributor"><a href="{{ url('ordersDistributeurs') }}">Distributeurs</a></li>
                    <li class="transfers_orders"><a href="{{ url('ordersTransfers') }}">Transfert</a></li>
                    <li><a href="{{ url('ordersHistory') }}">Historique</a></li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0)
            <li>
                <a href="{{ url('wrapOrder') }}">
                    <i class='bx bx-box'></i>
                    <span class="link_name">Emballer</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('wrapOrder') }}">Emballer</a></li>
                </ul>
            </li>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
            <li>
                <div class="iocn-link">
                <a href="#">
                    <i class='lni lni-delivery'></i>
                    <span class="link_name">Colissimo</span>
                </a>
                <i class='bx bxs-chevron-down arrow' ></i>
                </div>
                <ul class="sub-menu">
                    <li><a class="link_name" href="#">Colissimo</a></li>
                    <li><a href="{{ url('labels') }}">Étiquettes</a></li>
                        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0 ||
                        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
                            <li><a href="{{ url('missingLabels') }}">Étiquettes Manquantes</a></li>
                        @endif
                    <li><a href="{{ url('bordereaux') }}">Borderaux</a></li>
                </ul>
            </li>
        @endif

        <!-- Espace Sav -->
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  6)))
            <li>
                <a href="{{ url('sav') }}">
                    <i class='bx bx-support'></i>
                    <span class="link_name">Sav</span>
                </a>
                <ul class="sub-menu blank">
                    <li><a class="link_name" href="{{ url('sav') }}">Sav</a></li>
                </ul>
            </li>
        @endif

        <li>
            <div class="profile-details">
                <div class="profile-content">
                    <img src="assets/images/avatars/default_avatar.png" class="user-img" alt="user avatar">
                </div>
                <div class="name-job">
                    <div class="profile_name">{{ Auth()->user() ?  Auth()->user()->name : "Inconnu" }}</div>
                    <div class="job">@include('partials.account', ['role' => Auth()->user()->roles->toArray()])</div>
                </div>
                <a href="{{ url('logout') }}"><i class="bx bx-log-out"></i></a>

                <ul class="sub-menu blank sub_with_icon">
                    <li><a class="link_name" href="{{ url('logout') }}"><i class="bx bx-log-out"></i></a></li>
                </ul>
            </div>
        </li>


  </div>
  <section class="home-section close">
    <div class="home-content">
      <i class='bx bx-menu' ></i>
    </div>
  </section>
  
  <script>
    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
        arrow[i].addEventListener("click", (e)=>{
            let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
            arrowParent.classList.toggle("showMenu");
        });
    }

    let sidebar = document.querySelector(".sidebar");
    let sidebar_home = document.querySelector(".home-section");
    let sidebarBtn = document.querySelector(".bx-menu");

    sidebarBtn.addEventListener("click", ()=>{
        sidebar.classList.toggle("close");
        sidebar_home.classList.toggle("close");
        resize()
    });

    addEventListener("resize", (event) => {
        resize()
    })

    function resize(){
        if(!jQuery('.sidebar').hasClass('close') && document.body.clientWidth > 1025){
            jQuery(".page-wrapper").css('margin-left', '260px')
            jQuery(".page-footer").css('margin-left', '260px')
        } else if(jQuery('.sidebar').hasClass('close') && document.body.clientWidth > 1025){
            jQuery(".page-wrapper").css('margin-left', '85px')
            jQuery(".page-footer").css('margin-left', '85px')
        } else {
            jQuery(".page-wrapper").css('margin-left', '0px')
            jQuery(".page-footer").css('margin-left', '0px')
        }
    }

  </script>

