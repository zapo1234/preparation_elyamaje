<!-- Affichage des rôles utilisateurs -->

@foreach($role as $key => $r)
	@if(count($role) > 1)
		@if($key == count($role) - 1)
			/ {{ $r['role'] }}
		@else 
			{{ $r['role'] }}
		@endif
	@else
		{{ $r['role'] }}
	@endif
@endforeach

	