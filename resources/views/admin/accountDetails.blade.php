
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/cropper.css')}}" rel="stylesheet"/>
@endsection

@section("wrapper")
    <div class="page-wrapper profil_detail">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-baseline mb-3">
                <div class="breadcrumb-title pe-3">Configuration</div>
                <div class="ps-3 pe-3 breadcrumb-title">
                    <nav aria-label="breadcrumb breadcrumb-title">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Comptes</li>
                        </ol>
                    </nav>
                </div>
                <div class="ps-3 third_subtitle">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Détail profil</li>
                        </ol>
                    </nav>
                </div>
                @if($accessAccount)
                    <div class="ms-auto ms-auto-responsive">
                        <a href="{{ route('account') }}"><button type="button" class="btn btn-dark px-5">Retour aux comptes</button></a>
                    </div>
                @endif
            </div>

            @if ($errors->any())
                 <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    @foreach ($errors->all() as $error)
                        <div class="text-white">{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class="text-white">{{ session()->get('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class="text-white">{{ session()->get('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            <div class="main-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card card radius-10" style="height: 340px">
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <div class="edit_profil_image">
                                        <img src="{{ $user['picture'] ? 'storage/app/images/'.$user['picture'] : 'assets/images/avatars/default_avatar.png' }}" class="rounded-circle p-1 bg-dark" width="110">
                                        <button data-bs-toggle="modal" data-bs-target="#editProfilImage">
                                            <i class="fadeIn animated bx bx-edit"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <h4 class="font-bold">{{ $user['name'] }}</h4>
                                        <div class="d-flex flex-wrap justify-content-center">
                                            @foreach($user['roles_name'] as $keyRole => $role)
                                              
                                                @if($keyRole + 1 != count($user['roles_name']))
                                                    <span class="p-1 text-secondary">{{ $role }}</span>
                                                    <span class="p-1">/</span>
                                                @else 
                                                    <span class="p-1 text-secondary"> {{ $role }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card card radius-10">
                            <div class="card-body">
                                <form action="{{ route('updateAccountDetails') }}" method="POST">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Nom</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input required type="text" name="name" class="form-control" value="{{ $user['name'] }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Email</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input required type="text" name="email" class="form-control" value="{{ $user['email'] }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Mot de passe actuel</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input name="password" type="password" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="pass mb-0">Nouveau mot de passe</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input id="new_password" name="new_password" type="password" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="pass mb-0">Confirmation du mot de passe</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input id="new_password2" name="new_password2" type="password" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="text_password" style="position: absolute;left: 50%;" >
                                        <span class="same_password text-danger"></span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="submit" class="btn btn-primary px-4" value="Enregistrer">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($user['type'] == "shop" && Auth()->user()->hasRole(1))
                <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3">
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="widgets-icons rounded-circle mx-auto bg-light-success text-success mb-3"><i class="bx bx-check"></i>
                                    </div>
                                    <h4 class="font-bold my-1">{{ $histories['total_order'] }}</h4>
                                    <p class="mb-0 text-secondary">Commandes Enregistrées</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="widgets-icons rounded-circle mx-auto bg-light-danger text-danger mb-3"><i class="bx bx-cart-alt"></i>
                                    </div>
                                    <h4 class="my-1 font-bold">{{ $histories['average'] }}€</h4>
                                    <p class="mb-0 text-secondary">Panier Moyen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="widgets-icons rounded-circle mx-auto bg-light-info text-info mb-3"><i class="bx bx-time"></i>
                                    </div>
                                    <h4 class="my-1 font-bold">56K</h4>
                                    <p class="mb-0 text-secondary">Durée Moyenne</p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="widgets-icons rounded-circle mx-auto bg-light-info  text-info  mb-3"><i class="bx bx-euro"></i>
                                    </div>
                                    <h4 class="my-1 font-bold">{{ $histories['total_amount_order'] }}€</h4>
                                    <p class="mb-0 text-secondary">Total des Ventes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- List of commands for this user -->
                <div class="card card_table_mobile_responsive radius-10 w-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <div class="loading spinner-border text-dark" role="status"> 
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                        
                            <form method="GET" action="{{ route('admin.accountDetails') }}" class="d-flex d-none order_research">
                                <input value="{{ $user['user_id'] }}" name="user_id" class="custom_input" style="padding: 4px;" type="hidden">
                                <input value="{{ $parameter['created_at'] ?? '' }}" name="created_at" class="custom_input" style="padding: 4px;" type="date">
                                <input value="{{ $parameter['ref_order'] ?? '' }}" placeholder="Numéro de commande" name="ref_order" class="custom_input" style="padding: 4px;" type="text">
                                <button style="margin-left:10px" class="research_history_order d-flex align-items-center btn btn-primary" type="submit">Rechercher</button>
                            </form>

                            <table id="example" class="d-none w-100 table_list_order table_mobile_responsive table table-striped table-bordered">

                                <div class="d-none loading_show_detail_order w-100 d-flex justify-content-center">
                                    <div class="spinner-grow text-dark" role="status"> <span class="visually-hidden">Loading...</span></div>
                                </div>
                                
                                <thead>
                                    <tr>
                                        <th class="col-md-1" scope="col">Commande</th>
                                        <th class="col-md-4"scope="col">Client</th>
                                        <th class="col-md-4" scope="col">Montant</th>
                                        <th class="col-md-1" scope="col">Date de création</th>
                                        <th class="col-md-1" scope="col">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($histories['details'] as $histo)
                                        <tr>
                                            <td data-label="Commande">{{ $histo['ref_order'] }}</td>
                                            <td data-label="Client">{{ $histo['name'] }} {{ $histo['name'] != $histo['pname'] ? $histo['name'] : ''}}</td>
                                            <td data-label="Montant">{{ $histo['total_order_ttc'] }}</td>
                                            <td data-label="Date de création">{{ date('d/m/Y', strtotime($histo['created_at'])) }}</td>
                                            <td data-label="Status">
                                                <span class="radius-10 p-2 {{ $histo['status'] }}">{{ __('status.'.$histo['status']) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif


            <!-- Modal -->
            <div class="modal fade modal_radius" id="editProfilImage" tabindex="-1" role="dialog" aria-labelledby="editProfilImageLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body d-flex justify-content-center flex-column" style="min-height: 336px">
                            <div style="cursor:pointer; position:absolute; top: 10px; right: 15px;" class="d-flex justify-content-end">
                                <i data-bs-dismiss="modal" style="font-size: 20px" class="lni lni-close"></i>
                            </div>
                            <div class="container">
                                <form enctype="multipart/form-data" method="POST" class="updateImageProfil" action="{{ route('updateImageProfil') }}">
                                    @csrf
                                    <div class="row justify-content-center">
                                        <div class="col-lg-6 block_display_image" align="center">
                                            <div id="display_image_div">
                                                <div style="margin-bottom: -50px" class="loading_image d-none spinner-grow text-dark" role="status"> <span class="visually-hidden">Loading...</span></div>
                                                <img name="display_image_data" data-value="{{ $user['picture'] ? $user['picture'] : 'default_avatar.png' }}" width="150px" id="display_image_data" src="{{ $user['picture'] ? 'storage/app/images/'.$user['picture'] : 'assets/images/avatars/default_avatar.png' }}" alt="Picture">
                                            </div>
                                            <input type="hidden" name="cropped_image_data" id="cropped_image_data">
                                            <input type="hidden" name="user_id" value="{{ $user['user_id'] }}">
                                            <input type="file" name="browse_image" id="browse_image" class="form-control" style="display: none;">
                                            <button style="font-size:12px" id="upload_file" type="button" class="btn btn-primary mt-3" >Sélectionnez un fichier</button>


                                            
                                        </div> 
                                        <div class="col-lg-12 default_avatar_choice" align="center">
                                            <img name="avatar[]" class="avatar" data-value="avatar_1.png" width="65px" id="avatar_1" src="{{'assets/images/avatars/avatar_1.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_2.png" width="65px" id="avatar_2" src="{{'assets/images/avatars/avatar_2.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_3.png" width="65px" id="avatar_3" src="{{'assets/images/avatars/avatar_3.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_4.png" width="65px" id="avatar_4" src="{{'assets/images/avatars/avatar_4.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_5.png" width="65px" id="avatar_5" src="{{'assets/images/avatars/avatar_5.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_6.png" width="65px" id="avatar_6" src="{{'assets/images/avatars/avatar_6.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_7.png" width="65px" id="avatar_7" src="{{'assets/images/avatars/avatar_7.png'}}" alt="Picture">
                                            <img name="avatar[]" class="avatar" data-value="avatar_8.png" width="65px" id="avatar_8" src="{{'assets/images/avatars/avatar_8.png'}}" alt="Picture">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button> -->
                            <button id="crop_button" type="button" class="btn btn-primary px-5">Valider</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


@endsection


@section("script")
    <script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{asset('assets/js/leaderHistory.js')}}"></script>
    <script src="{{asset('assets/js/cropper.min.js')}}"></script>
    <script src="{{asset('assets/js/account.js')}}"></script>

@endsection


