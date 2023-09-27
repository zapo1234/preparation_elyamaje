@if(session()->has('error'))
    <div id="error-message" class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
        <div class="text-white"> {{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        setTimeout(function() {
            $('#error-message').fadeOut();
        }, 10000);
    </script>
@endif