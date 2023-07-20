<!-- Affichage de la catégorie actuelle -->

@php $level = $level ?? 0 @endphp
@php $category_id = $category_id ?? $category['category_id_woocommerce'] @endphp


@if($level == 0)
	<option value="{{ $category['name'] }}">{{ $category['name'] }}</option>
	<!-- <tr class="category sub_category_{{ $level}}">  -->
		<!-- <td data-label="Nom" class="td_{{ $category['category_id_woocommerce'] }}"> -->


			<!-- Vérification des sous-catégories -->
			@if (!empty($category['sub_category']))
				@php $level++ @endphp
				@foreach ($category['sub_category'] as $subCategory)
					<!-- Appeler la vue récursive pour afficher les sous-catégories et les sous-sous-catégories -->
					<option value="{{ $subCategory['name'] }}"><?php echo str_repeat('&nbsp;', $level*2.5); ?> {{$subCategory['name'] }}</option>
					@include('partials.category_select', ['category' => $subCategory, 'level' => $level])
				@endforeach
			@endif
		<!-- </td> -->
	<!-- </tr> -->
@else 
	<!-- Vérification des sous-catégories -->
	@if (!empty($category['sub_category']))
		@php $level++ @endphp
		@foreach ($category['sub_category'] as $subCategory)
			<!-- Appeler la vue récursive pour afficher les sous-catégories et les sous-sous-catégories -->
			<option value="{{ $subCategory['name'] }}"><?php echo str_repeat('&nbsp;', $level*2.5); ?>  {{$subCategory['name'] }}</option>
			@include('partials.category_select', ['category' => $subCategory, 'level' => $level])
		@endforeach
	@endif
@endif

