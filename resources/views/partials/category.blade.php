<!-- Affichage de la catégorie actuelle -->

@php $level = $level ?? 0 @endphp
@php $category_id = $category_id ?? $category['category_id_woocommerce'] @endphp


@if($level == 0)
	<tr class="category sub_category_{{ $level}}"> 
		<td data-label="Nom" class="td_{{ $category['category_id_woocommerce'] }}">
			@if (!empty($category['sub_category']))
				<i id="{{ $category['category_id_woocommerce'] }}" class="show_sub_category bx bx-plus"></i>
			@endif
			{{ $category['name'] }}


			<!-- Vérification des sous-catégories -->
			@if (!empty($category['sub_category']))
				@php $level++ @endphp
				@foreach ($category['sub_category'] as $subCategory)
					<!-- Appeler la vue récursive pour afficher les sous-catégories et les sous-sous-catégories -->
					
					<div class="category category_{{ $subCategory['parent_category_id'] }} sub_category sub_category_{{ $level}}"> 
						<div class="align-items-center d-flex w-100 justify-content-between">
							<div>	
								@if (!empty($subCategory['sub_category']))
									<i id="{{ $subCategory['category_id_woocommerce'] }}" class="show_sub_category bx bx-plus"></i>
								@else
									<i class="minus bx bx-minus"></i>
								@endif
								<span class="{{ !empty($subCategory['sub_category']) ? 'hassub' : 'hasnotsub' }}">{{ $subCategory['name'] }}</span>
							</div>
							<select data-parent="{{ $category_id }}" id="{{ $subCategory['id'] }}" data-id="{{ $subCategory['category_id_woocommerce'] }}" class="update_order_display">
								@for($i = 0; $i < 106; $i++)
									@if($subCategory['order_display'] == $i)
										<option value="{{ $i }}" selected>{{ $i }}</option>
									@else 
										<option value="{{ $i }}">{{ $i }}</option>
									@endif
								@endfor
							</select>
						</div>
						@include('partials.category', ['category' => $subCategory, 'level' => $level])
					</div>
				@endforeach
			@endif
		</td>
		<td  data-label="Ordre">
			<select id="{{ $category['id'] }}" data-id="{{ $category['category_id_woocommerce'] }}" class="select_parent_menu update_order_display">
				@for($i = 0; $i < 106; $i++)
					@if($category['order_display'] == $i)
						<option value="{{ $i }}" selected>{{ $i }}</option>
					@else 
						<option value="{{ $i }}">{{ $i }}</option>
					@endif
				@endfor
			</select>
		</td>
	</tr>
@else 
	<!-- Vérification des sous-catégories -->
	@if (!empty($category['sub_category']))
		@php $level++ @endphp
		@foreach ($category['sub_category'] as $subCategory)
			<!-- Appeler la vue récursive pour afficher les sous-catégories et les sous-sous-catégories -->
			
			<div class=" category category_{{ $subCategory['parent_category_id'] }} sub_category sub_category_{{ $level}}"> 
				<div class="align-items-center d-flex w-100 justify-content-between">
					<div>	
						@if (!empty($subCategory['sub_category']))
							<i id="{{ $subCategory['category_id_woocommerce'] }}" class="show_sub_category bx bx-plus"></i>
						@else
							<i class="minus bx bx-minus"></i>
						@endif
						<span class="{{ !empty($subCategory['sub_category']) ? 'hassub' : 'hasnotsub' }}">{{ $subCategory['name'] }}</span>
					</div>
				
					<select data-parent="{{ $category_id }}" id="{{ $subCategory['id'] }}" data-id="{{ $subCategory['category_id_woocommerce'] }}" class="update_order_display">
						@for($i = 0; $i < 106; $i++)
							@if($subCategory['order_display'] == $i)
								<option value="{{ $i }}" selected>{{ $i }}</option>
							@else 
								<option value="{{ $i }}">{{ $i }}</option>
							@endif
						@endfor
					</select>
				</div>
			@include('partials.category', ['category' => $subCategory, 'level' => $level])
			</div>
		@endforeach
	@endif
@endif

