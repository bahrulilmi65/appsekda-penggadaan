<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">
        <div class="display-1 fw-bold text-danger">403</div>
        <h3>Akses Ditolak</h3>
        <p class="text-muted">{{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary me-2">Kembali</a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Beranda</a>
    </div>
</body>
</html>
