<div class="sidebar close">
    <div class="logo-details">
      <!-- <i class='bx bxl-c-plus-plus'></i> -->
      <!-- <span class="logo_name">CodingLab</span> -->
        <div class="div_short_icon">

            <img src="{{(asset('assets/images/short_logo_noir.png'))}}" class="logo-short" alt="logo icon">
        </div>
        <img src="{{ asset('assets/images/elyamaje_logo_long_noir.png') }}" class="logo_name logo-icon" alt="logo icon">
    </div>
    <ul class="nav-links metismenu" id="menu">
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
            <div class="div_icon">
                <li>
                    <a href="{{ url('dashboard') }}">
                        <i class='bx bx-grid-alt' ></i>
                        <span class="link_name">Tableau de bord</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('dashboard') }}">Tableau de bord</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('getVieuxSplay') }}">
                        <i class='bx bx-transfer'></i>
                        <span class="alert-count" style="left: 40px"  id="alerte_liste_total">{{(session()->has('alerte_reassortEnAttente'))? (session()->get('alerte_reassortEnAttente')) : 0}}</span>
                        <span class="link_name">Transferts</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li>
                            <a class="link_name" href="{{ url('getVieuxSplay') }}">
                                Transferts
                                <span class="alert-count" style="left: 100px" id="alerte_reassort">{{session()->has('alerte_reassortEnAttente')? session()->get('alerte_reassortEnAttente') : 0}}</span>

                            </a>
                        </li>
                    </ul>
                </li>
            </div>
        @endif



        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0)
            <div class="div_icon">
                <li>
                    <a href="{{ url('indexAdmin') }}">
                        <i class='bx bx-grid-alt'></i>
                        <span class="link_name">Tableau de bord</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('indexAdmin') }}">Tableau de bord</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('analytics') }}" id="tableau_de_bord_link">
                        <i class='bx bx-line-chart-down'></i>
                        <span class="link_name">Analytics</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('analytics') }}">Analytics</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('orderfacturer') }}">
                        <i class='bx bx-cloud-download'></i>
                        <span class="link_name">Dolibarr</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('orderfacturer') }}">Dolibarr</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('refreshtiers') }}">
                        <i class='bx bx-import'></i>
                        <span class="link_name">Import clients</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('refreshtiers') }}">Import clients</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
                        <li><a href="{{ url('colissimo') }}">Étiquettes</a></li>
                        <li><a href="{{ url('configDolibarr') }}">Dolibarr</a></li>
                        <li><a href="{{ url('paymentTerminal') }}">Terminaux</a></li>
                        <li><a href="{{ url('caisse') }}">Caisses</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('getVieuxSplay') }}">
                        <i class='bx bx-transfer'></i>
                        <span class="alert-count" style="left: 40px"  id="alerte_liste_total">{{(session()->has('alerte_stockReassort') && session()->has('alerte_reassortEnAttente'))? (session()->get('alerte_stockReassort') + session()->get('alerte_reassortEnAttente')) : 0}}</span>
                        <span class="link_name">Transferts</span>
                    </a>

                    <ul class="sub-menu beauty_prof_menu">
                        <li>
                            <a class="link_name" href="{{ url('getVieuxSplay') }}">
                                Transferts
                                <span class="alert-count" style="left: 100px" id="alerte_reassort">{{session()->has('alerte_reassortEnAttente')? session()->get('alerte_reassortEnAttente') : 0}}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('alertStocks/15/1') }}">State de ventes</a>
                        </li>
                        <li>
                            <a href="{{ url('listeAlerte') }}">
                                Liste des alertes
                                <span class="alert-count" style="left: 130px" id="alerte_liste">{{session()->has('alerte_stockReassort')? session()->get('alerte_stockReassort') : 0}}</span>
                            </a>
                        </li>

                    </ul>
                
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('errorLogs') }}">
                        <i class='bx bx-news'></i>
                        <span class="link_name">Journal</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('errorLogs') }}">Journal</a></li>
                    </ul>
                </li> 
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
                    <a href="#">
                        <span class="link_text font-bold text-dark">BP</span>
                        <span class="link_name">Beauty Prof's</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow' ></i>
                    </div>
                    <ul class="sub-menu beauty_prof_menu">
                        <li><a class="link_name" href="#">Beauty Prof's</a></li>
                        <li><a href="{{ url('seller') }}">Analytics</a></li>
                        <li><a href="{{ url('cashierWaiting') }}">Commandes en attente</a></li>
                        <li><a href="{{ url('cashier') }}">Caisse</a></li>
                        <li><a href="{{ url('beautyProfHistory') }}">Historique</a></li>
                        <li><a href="{{ url('stockscat') }}">Gestion stocks</a></li>
                        <li><a href="{{ url('generateinvoices') }}">Renvoyer une facture au client</a></li>
                    </ul>
                </li>
            </div>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  2)) > 0)
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0)
            <div class="div_icon">
                <li>
                    <a href="{{ url('wrapOrder') }}">
                        <i class='bx bx-box'></i>
                        <span class="link_name">Emballer</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('wrapOrder') }}">Emballer</a></li>
                    </ul>
                </li>
            </div>
        @endif
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  3)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0 || 
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0 ||
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  6)) > 0)
            <div class="div_icon">
                <li>
                    <div class="icon-link">
                    <a href="#">
                        <i class='lni lni-delivery'></i>
                        <span class="link_name">Expédition</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow' ></i>
                    </div>
                    <ul class="sub-menu">
                        <li><a class="link_name" href="#">Expédition</a></li>
                        <li><a href="{{ url('labels') }}">Étiquettes</a></li>
                            @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0 ||
                            count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
                                <li><a href="{{ url('missingLabels') }}">Étiquettes Manquantes</a></li>
                            @endif
                        <li><a href="{{ url('bordereaux') }}">Borderaux</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
                    <a href="#">
                        <i class='lni lni-mailchimp'></i>
                        <span class="link_name">Commandes fournisseur</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow' ></i>
                    </div>
                    <ul class="sub-menu">
                        <li><a class="link_name" href="#">Commandes fournisseur</a></li>
                        <li><a href="{{ route('listeSupplierorders') }}">Listes des commande</a></li>
                    </ul>
                </li>
            </div>
        @endif

        <!-- Espace Sav -->
        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  6)))
            <div class="div_icon">
                <li>
                    <div class="icon-link">
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
            </div>
            <div class="div_icon">
                <li>
                    <a href="{{ url('sav') }}">
                        <i class='bx bx-support'></i>
                        <span class="link_name">Sav</span>
                    </a>
                    <ul class="sub-menu blank">
                        <li><a class="link_name" href="{{ url('sav') }}">Sav</a></li>
                    </ul>
                </li>
            </div>
            <div class="div_icon">
                <li>
                    <div class="icon-link">
                    <a href="#">
                        <span class="link_text font-bold text-dark">BP</span>
                        <span class="link_name">Beauty Prof's</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow' ></i>
                    </div>
                    <ul class="sub-menu beauty_prof_menu">
                        <li><a class="link_name" href="#">Beauty Prof's</a></li>
                        <li><a href="{{ url('cashierWaiting') }}">Commandes en attente</a></li>
                        <li><a href="{{ url('beautyProfHistory') }}">Historique</a></li>
                    </ul>
                </li>
            </div>
        @endif
            <div>
                <li>
                    <div class="profile-details">
                        <div class="profile-content">
                            

                            <img src="{{ Auth()->user()->picture ? 'storage/app/images/'.Auth()->user()->picture : asset('assets/images/avatars/default_avatar.png') }}" class="user-img" alt="user avatar">
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
            jQuery(".page-footer").css('margin-left', '78px')
        } else {
            jQuery(".page-wrapper").css('margin-left', '0px')
            jQuery(".page-footer").css('margin-left', '0px')
        }
    }

  </script>