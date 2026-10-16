@extends('layouts.app')
@section('title', 'Audit Log')
@section('breadcrumb')
    <li class="breadcrumb-item">Sistem</li>
    <li class="breadcrumb-item active">Audit Log</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Audit Log Aktivitas</h4>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-2"><select name="module" class="form-select form-select-sm"><option value="">Semua Modul</option>
            @foreach(['booking','payment','facility','inventory','customer','user','auth','laundry','damage','return'] as $m)
            <option value="{{ $m }}" {{ request('module')===$m?'selected':'' }}>{{ ucfirst($m) }}</option>@endforeach
        </select></div>
        <div class="col-md-2"><select name="action" class="form-select form-select-sm"><option value="">Semua Aksi</option>
            @foreach(['create','update','delete','login','logout','approve','reject','verify'] as $a)
            <option value="{{ $a }}" {{ request('action')===$a?'selected':'' }}>{{ ucfirst($a) }}</option>@endforeach
        </select></div>
        <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
        <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Filter</button></div>
    </form>
</div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive">
    <table class="table table-hover mb-0 small">
        <thead class="table-light"><tr><th>Waktu</th><th>User</th><th>Modul</th><th>Aksi</th><th>Deskripsi</th><th>IP</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-muted" style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                <td class="fw-semibold">{{ $log->user_name }}</td>
                <td><span class="badge bg-info">{{ $log->module }}</span></td>
                <td><span class="badge bg-{{ in_array($log->action,['delete','reject']) ? 'danger' : (in_array($log->action,['create','approve','verify']) ? 'success' : 'secondary') }}">{{ $log->action }}</span></td>
                <td>{{ $log->description }}</td>
                <td class="text-muted">{{ $log->ip_address }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada log aktivitas</td></tr>
            @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $logs->links('pagination::bootstrap-5') }}</div></div>
@endsection

