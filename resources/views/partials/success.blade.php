@if(session()->has('success'))
<div id="success-message" class="alert custom-page-style" style="text-align: center;color:#e9186594;">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(function() {
            $('#success-message').remove();
        }, 5000);
    </script>
@endif
