@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pengaturan</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Pengaturan Sistem</h4>
<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf @method('PUT')
    @foreach($settings as $group => $items)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold text-capitalize"><i class="fas fa-cog me-2 text-primary"></i>{{ ucfirst($group) }}</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($items as $setting)
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">{{ $setting->label ?? $setting->key }}</label>
                    @if($setting->type === 'boolean')
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}><label class="form-check-label small">Aktif</label></div>
                    @else
                    <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}" name="settings[{{ $setting->key }}]" class="form-control form-control-sm" value="{{ $setting->value }}">
                    @endif
                    @if($setting->description)<div class="form-text">{{ $setting->description }}</div>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Pengaturan</button>
</form>
@endsection
