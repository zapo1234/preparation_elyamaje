@if(session()->has('success'))
    <div id="success-message" class="alert alert-success border-0 bg-success alert-dismissible fade show">
        <div class="text-white">{{ session()->get('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(function() {
            $('#success-message').fadeOut();
        }, 4000);
    </script>

@endif
@if(session()->has('error'))
    <div id="error-message" class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
        <div class="text-white">{{ session()->get('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(function() {
            $('#error-message').fadeOut();
        }, 4000);
    </script>

@endif