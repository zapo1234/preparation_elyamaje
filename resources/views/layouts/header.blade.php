<header>
        @php
            use App\Models\Notification;
            $notification = Notification::where('to_user', Auth()->user()->id)->where('is_read', 0)->get();
            $count_notif = 0;
            $lists = [];

            foreach($notification as $not) {
                $count_notif = $count_notif + 1;
                $lists[] = [
                    'from_user' => $not['from_user'],
                    'to_user' => $not['to_user'],
                    'type' => __('status.'.$not['type']),
                    'order_id' => $not['order_id'],
                    'date' => $not['created_at']->diff(date('Y-m-d H:i:s')),
                    'detail' => $not['detail']
                ];
            }

            if($count_notif > 99){
                $count_notif = "99+";
            }

            function format_interval(DateInterval $interval) {
                $result = "";

                if ($interval->y) { $result = $interval->format("%y ans "); }
                if ($interval->m) { $result .= $interval->format("%m mois "); }
                if ($interval->d && (!$interval->m && !$interval->y)) { $result .= $interval->format("%d jours "); }
                if ($interval->h && (!$interval->m && !$interval->y)) { $result .= $interval->format("%h heures "); }
                if ($interval->i && (!$interval->d && !$interval->m && !$interval->y)) { $result .= $interval->format("%i minutes "); }
                if ($interval->s && (!$interval->h && !$interval->d && !$interval->i)) { $result .= $interval->format("%s secondes "); }

                return $result;
            }
        @endphp 

            <div class="topbar d-flex align-items-center">
                <nav class="navbar navbar-expand">
                    <!-- <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                    </div> -->
         
                    <!-- <div class="search-bar flex-grow-1">
                        <div class="position-relative search-bar-box">
                            <input type="text" class="form-control search-control" placeholder="Type to search..."> <span class="position-absolute top-50 search-show translate-middle-y"><i class='bx bx-search'></i></span>
                            <span class="position-absolute top-50 search-close translate-middle-y"><i class='bx bx-x'></i></span>
                        </div>
                    </div> -->
                    <div class="top-menu ms-auto">
                        <ul class="navbar-nav align-items-center">

                            @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0 ||
                            count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
                                <li class="nav-item dropdown dropdown-large close_day">
                                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button">
                                        <i class="bx bx-lock-open"></i>
                                    </a>
                                </li>
                            @endif
                        
                            <li class="nav-item dropdown dropdown-large">
                                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">{{ $count_notif }}</span>
                                    <i class='bx bx-bell'></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:;">
                                        <div class="msg-header">
                                            <p class="msg-header-title">Notifications</p>
                                            <!-- <p class="msg-header-clear ms-auto">Marks all as read</p> -->
                                        </div>
                                    </a>
                                    <div class="header-notifications-list">
                                        @foreach($lists as $list)
                                            <a class="dropdown-item notification_list" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-warning text-primary"><i class="text-light bx bx-box"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">{{ $list['type'] }} {{ $list['order_id'] ? '#'.$list['order_id'] : '' }}</h6>
                                                        <span class="msg-info">{{ $list['detail'] }}</span>
                                                    </div>
                                                    
                                                </div>
                                                <div class="w-100 d-flex justify-content-end">
                                                    <span class="msg-time float-end">{{ format_interval($list['date']) }}</span>
                                                </div>
                                            </a>
                                        @endforeach
                                        @if($count_notif == 0)
                                            <span class="empty_notification mt-3 msg-time d-flex justify-content-center w-100">Aucune notification non lue</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('notifications.all') }}">
                                        <div class="text-center msg-footer">Voir toutes les notifications</div>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="user-box dropdown border-light-2">
                        <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth()->user()->picture ? 'storage/app/images/'.Auth()->user()->picture : asset('assets/images/avatars/default_avatar.png') }}" class="user-img" alt="user avatar">
                            <div class="user-info ps-3">
                                <p class="user-name mb-0">{{ Auth()->user() ?  Auth()->user()->name : "Inconnu" }}</p>
                                <p class="designattion mb-0"> @include('partials.account', ['role' => Auth()->user()->roles->toArray()])</p>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.accountDetails') }}?user_id={{ Auth()->user()->id }}"><i class='bx bx-user'></i><span>Profil</span></a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"><i class='bx bx-log-out-circle'></i><span>Déconnexion</span></a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>

        @if(count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  1)) > 0 ||
        count(array_keys(array_column(Auth()->user()->roles->toArray(), "id"),  4)) > 0)
            <!-- Modal clôture de journée -->
            <div class="modal fade" id="closeDayModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <!-- <form class="w-100" method="POST" action="{{ route('leader.closeDay') }}">
                        @csrf -->
                        <div class="modal-content">
                            <div class="modal-body">
                                <h2 class="text-center">Clôturer la journée ?</h2>
                                <div class="w-100 text-center d-flex justify-content-center">
                                    <span class="w-75 response_close_day"></span>
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-center footer_1">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Non</button>
                                <button type="button" class="valid_close_day btn btn-primary">
                                    <div class="loading_close_day d-none spinner-border spinner-border-sm text-light" role="status"> <span class="visually-hidden">Loading...</span></div>
                                    <span class="close_day_text">Oui</span>
                                </button>
                            </div>
                            <div class="modal-footer d-flex justify-content-center d-none footer_2">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    <!-- </form> -->
                </div>
            </div>
        @endif


        