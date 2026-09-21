@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#0284C7',
            timer: 4000,
            timerProgressBar: true,
            backdrop: 'rgba(15,23,42,.5)',
        });
    });
</script>
@endif

@if(session('errors') && session('errors')->any())
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            position: 'center',
            icon: 'error',
            title: @json(session('errors')->first()),
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#DC2626',
            backdrop: 'rgba(15,23,42,.5)',
        });
    });
</script>
@endif