// Create close tab mobile
function filter_mobile(){
    if($(".page-wrapper").find('.dashboard_leader').length == 0 && $(".dataTables_length ").length > 0 && ($("#example_length .custom_input").length > 1 || $("#example_length .select2_custom").length > 1)){
        if($(".filter_mobile").length == 0){
            $(".table_mobile_responsive").before(`
            <div class="filter_mobile">
                <div class="box_filter">
                    <i class="font-20 bx bx-filter-alt"></i>
                </div>
				<div class="d-none filter_tab">
					<span class="filter_tab_title">Filtres</span>

				</div>
				<div class="other_element d-flex"></div>
            </div>
        `)

        if($(".modal_filter_mobile").length == 0){
			$("#example_wrapper > .row:first-child").appendTo('.filter_tab')
			$('#example_filter').appendTo('.other_element')
			$('select[name="example_length"]').parent().last().appendTo('.other_element')
        }

            $('body').on('click', '.box_filter', function () {
                $(".filter_tab").toggleClass('d-none')
            })
        }
    } else {
        if($(".filter_mobile").length > 0){
            $(".modal-body > .row").last().prependTo('#example_wrapper')
            $('select[name="example_length"]').parent().last().prependTo('.dataTables_length')
            $(".filter_mobile").remove()
            $(".modal_filter_mobile").remove()
        }
    }
}

// Close filter tab when click outside
document.addEventListener('click', function(event) {

	if(!$(".filter_tab").hasClass('d-none')){
		var targetDiv = document.getElementsByClassName('filter_tab')[0];
		var targetDiv2 = document.getElementsByClassName('box_filter')[0];

		if(targetDiv && targetDiv2){
			var clickedInside = targetDiv.contains(event.target);
			var clickedInside2 = targetDiv2.contains(event.target);
	
			if (!clickedInside && !clickedInside2) {
				$(".filter_tab").toggleClass('d-none')
			}
		}
		
	}
});

$(function () {

	"use strict";
	/* perfect scrol bar */

	// Clôture d ejournée
	$(".close_day").on('click', function(){
		$(".footer_1").removeClass('d-none')
		$(".footer_2").addClass('d-none')
		$(".response_close_day").text('')
		$("#closeDayModal").modal('show')
	})

	$(".valid_close_day").on('click', function(){
		$(".loading_close_day").removeClass('d-none')
		$(".close_day_text").addClass('d-none')
		$(".valid_close_day").attr('disabled', true)
	
		$.ajax({
			url: "orderinvoices?from_js=true",
			method: 'GET',
		}).done(function(data) {
			$(".loading_close_day").addClass('d-none')
			$(".close_day_text").removeClass('d-none')
			$(".valid_close_day").attr('disabled', false)
			$(".footer_1").addClass('d-none')
			$(".footer_2").removeClass('d-none')
			$(".response_close_day").removeClass('text-success')
			$(".response_close_day").removeClass('text-danger')
			$(".response_close_day").removeClass('text-warning')

			if(JSON.parse(data).success){
				if(!JSON.parse(data).diff){
					$(".response_close_day").addClass('text-success')
					$(".response_close_day").text(JSON.parse(data).message)
				} else {
					$(".response_close_day").addClass('text-warning')
					$(".response_close_day").text(JSON.parse(data).message)
				}
			} else {
				$(".response_close_day").addClass('text-danger')
				$(".response_close_day").text(JSON.parse(data).message)
			}
		});
	})

	// search bar
	$(".mobile-search-icon").on("click", function () {
		$(".search-bar").addClass("full-search-bar");
		$(".page-wrapper").addClass("search-overlay");
	});
	$(".search-close").on("click", function () {
		$(".search-bar").removeClass("full-search-bar");
		$(".page-wrapper").removeClass("search-overlay");
	});
	$(".mobile-toggle-menu").on("click", function () {
		$(".wrapper").addClass("toggled");
	});
	// toggle menu button
	$(".toggle-icon").click(function () {
		if ($(".wrapper").hasClass("toggled")) {
			// unpin sidebar when hovered
			$(".wrapper").removeClass("toggled");
			$(".sidebar-wrapper").unbind("hover");
		} else {
			$(".wrapper").addClass("toggled");
			$(".sidebar-wrapper").hover(function () {
				$(".wrapper").addClass("sidebar-hovered");
			}, function () {
				$(".wrapper").removeClass("sidebar-hovered");
			})
		}
	});
	/* Back To Top */
	$(document).ready(function () {
		$(window).on("scroll", function () {
			if ($(this).scrollTop() > 300) {
				$('.back-to-top').fadeIn();
			} else {
				$('.back-to-top').fadeOut();
			}
		});
		$('.back-to-top').on("click", function () {
			$("html, body").animate({
				scrollTop: 0
			}, 600);
			return false;
		});
	});
	// === sidebar menu activation js
	$(function () {
		var currentLocation = window.location.href;
		
		$(".metismenu li a").each(function () {
			var $this = $(this);
			
			if ($this.attr("href") === currentLocation) {
				$this.addClass("mm-active").parents("ul.sub-menu").addClass("mm-show");
				$this.closest(".div_icon").addClass("mm-active");
				return false; // Sortir de la boucle each dès qu'un élément correspondant est trouvé
			}
		});
	});
	// metismenu
	$(function () {
		$('#menu').metisMenu();
	});
	// chat toggle
	$(".chat-toggle-btn").on("click", function () {
		$(".chat-wrapper").toggleClass("chat-toggled");
	});
	$(".chat-toggle-btn-mobile").on("click", function () {
		$(".chat-wrapper").removeClass("chat-toggled");
	});
	// email toggle
	$(".email-toggle-btn").on("click", function () {
		$(".email-wrapper").toggleClass("email-toggled");
	});
	$(".email-toggle-btn-mobile").on("click", function () {
		$(".email-wrapper").removeClass("email-toggled");
	});
	// compose mail
	$(".compose-mail-btn").on("click", function () {
		$(".compose-mail-popup").show();
	});
	$(".compose-mail-close").on("click", function () {
		$(".compose-mail-popup").hide();
	});
	/*switcher*/
	$(".switcher-btn").on("click", function () {
		$(".switcher-wrapper").toggleClass("switcher-toggled");
	});
	$(".close-switcher").on("click", function () {
		$(".switcher-wrapper").removeClass("switcher-toggled");
	});
	$("#lightmode").on("click", function () {
		$('html').attr('class', 'light-theme');
	});
	$("#darkmode").on("click", function () {
		$('html').attr('class', 'dark-theme');
	});
	$("#semidark").on("click", function () {
		$('html').attr('class', 'semi-dark');
	});
	$("#minimaltheme").on("click", function () {
		$('html').attr('class', 'minimal-theme');
	});
	$("#headercolor1").on("click", function () {
		$("html").addClass("color-header headercolor1");
		$("html").removeClass("headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8");
	});
	$("#headercolor2").on("click", function () {
		$("html").addClass("color-header headercolor2");
		$("html").removeClass("headercolor1 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8");
	});
	$("#headercolor3").on("click", function () {
		$("html").addClass("color-header headercolor3");
		$("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8");
	});
	$("#headercolor4").on("click", function () {
		$("html").addClass("color-header headercolor4");
		$("html").removeClass("headercolor1 headercolor2 headercolor3 headercolor5 headercolor6 headercolor7 headercolor8");
	});
	$("#headercolor5").on("click", function () {
		$("html").addClass("color-header headercolor5");
		$("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor3 headercolor6 headercolor7 headercolor8");
	});
	$("#headercolor6").on("click", function () {
		$("html").addClass("color-header headercolor6");
		$("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor3 headercolor7 headercolor8");
	});
	$("#headercolor7").on("click", function () {
		$("html").addClass("color-header headercolor7");
		$("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor3 headercolor8");
	});
	$("#headercolor8").on("click", function () {
		$("html").addClass("color-header headercolor8");
		$("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor3");
	});
	
	
	
   // sidebar colors 


    $('#sidebarcolor1').click(theme1);
    $('#sidebarcolor2').click(theme2);
    $('#sidebarcolor3').click(theme3);
    $('#sidebarcolor4').click(theme4);
    $('#sidebarcolor5').click(theme5);
    $('#sidebarcolor6').click(theme6);
    $('#sidebarcolor7').click(theme7);
    $('#sidebarcolor8').click(theme8);

    function theme1() {
      $('html').attr('class', 'color-sidebar sidebarcolor1');
    }

    function theme2() {
      $('html').attr('class', 'color-sidebar sidebarcolor2');
    }

    function theme3() {
      $('html').attr('class', 'color-sidebar sidebarcolor3');
    }

    function theme4() {
      $('html').attr('class', 'color-sidebar sidebarcolor4');
    }
	
	function theme5() {
      $('html').attr('class', 'color-sidebar sidebarcolor5');
    }
	
	function theme6() {
      $('html').attr('class', 'color-sidebar sidebarcolor6');
    }

    function theme7() {
      $('html').attr('class', 'color-sidebar sidebarcolor7');
    }

    function theme8() {
      $('html').attr('class', 'color-sidebar sidebarcolor8');
    }

});


$( document ).ready(function() {

	$('body').on( 'init.dt', function ( e, ctx ) {
		filter_mobile()
	})

	if($.fn.dataTable){
		$.extend(true, $.fn.dataTable.defaults, {
			"showNEntries" : false,
			"info":     false,
			"language": {
				"emptyTable": "Aucune donnée",
				"search": "",
				"searchPlaceholder": "Rechercher...",
				"lengthMenu": "_MENU_",
				"paginate": {
					"previous": "<i class='fadeIn animated bx bx-chevron-left'></i>",
					"next": "<i class='fadeIn animated bx bx-chevron-right'></i>"
				}
			},
			"oLanguage": {
				"sInfo" : "Affichage des entrées de _START_ à _END_ sur un total de _TOTAL_ ",
			},
			
		
		});
	}

	$(".dropdown-toggle-nocaret").on("click", function(){
		if($(".alert-count").text() != 0){
			$.ajax({
				url: "notifications",
				method: 'GET',
			}).done(function() {
				
			});
		}
	})
});

// Notification Pusher
function notificationsListener(user_role_logged){
	var roles = [];

	if(user_role_logged){
		user_role_logged = JSON.parse(user_role_logged)
		Object.entries(user_role_logged).forEach(([key, value]) => {
			roles.push(value.pivot.role_id);
		});
	}

	var pusher = new Pusher('1095a816bf393d278517', {
		cluster: 'eu',
		forceTLS: true
	});

	var channel = pusher.subscribe('preparation');
	channel.bind('notification', function(data) {
		if(roles.includes(data.message.role)){
			// Notification for leader when order is partially completed
			if(data.message.type == "partial_order"){
				$(".alert-count").removeClass('animation_zoom')
				$(".alert-count").addClass('animation_zoom')
				$(".empty_notification").remove()
				$(".alert-count").text(parseInt($(".alert-count").text()) + 1)
				$(".header-notifications-list").append(`
					<a class="dropdown-item notification_list" href="javascript:;">
						<div class="d-flex align-items-center">
							<div class="notify bg-warning text-primary"><i class="text-light bx bx-box"></i>
							</div>
							<div class="flex-grow-1">
								<h6 class="msg-name">Commande partielle `+data.message.order_id+`</h6>
								<span class="msg-info">`+data.message.data+`</span>
							</div>
						</div>
						<div class="w-100 d-flex justify-content-end">
							<span class="msg-time float-end">1s</span>
						</div>
					</a>
				`)
			// Notification for preparateur if one of his orders is no longer assigned to him
			} else if(data.message.type == "order_attribution_updated"){
				// Checks if the user has this command
				$( ".tab-pane .show_order" ).each(function( index ) {
					if(data.message.order_id == $( this ).attr('id')){
						$("#modalInfo").remove()
						$("body").append(`
							<div class="modal_reset_order modal fade" id="modalInfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
											<h2 class="text-center">Attention, la commande ${data.message.order_id} à été réatribuée </h2>
											<div class="mt-3 w-100 d-flex justify-content-center">
												<button onClick="window.location.reload();" type="button" class="btn btn-dark px-5">Fermer</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						`)

						$('#modalInfo').modal({
							backdrop: 'static',
							keyboard: false
						})
						$("#modalInfo").modal('show')
					}
				});
			}
		}
	});
}


function notificationAlertStock(url_notification,_token){



	var pusher = new Pusher('1095a816bf393d278517', {
		cluster: 'eu',
		forceTLS: true
	});

	var channel = pusher.subscribe('preparation');
	channel.bind('notification', function(data) {
		if(data.message.type == "alerteStock"){

			cle = data.message.cle;
			value = data.message.value;

			$.ajax({
				url: url_notification,
				method: "POST",
				async: false,
				data : {_token: _token, cle: cle, value: value}
			}).done(function(data) {
				if (data.response) {
					if ($("#alerte_liste").html()) {
						if (cle == "alerte_stockReassort") {
							$("#alerte_liste").html(data.data.nouvelleValeur)
							$("#alerte_liste_total").html(parseInt(data.data.nouvelleValeur) + parseInt($("#alerte_reassort").html()))
						}else if(cle == "alerte_reassortEnAttente"){
							$("#alerte_reassort").html(data.data.nouvelleValeur)
							$("#alerte_liste_total").html(parseInt(data.data.nouvelleValeur) + parseInt($("#alerte_liste").html()))
						}
					}else{
						if (cle == "alerte_reassortEnAttente") {
							$("#alerte_reassort").html(data.data.nouvelleValeur)
							$("#alerte_liste_total").html(parseInt(data.data.nouvelleValeur))
						}
					}
					

				}else{
					console.log('erreur pusher');
				}
			});









		}
	});
}