@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Verifikasi Institusi</h1>
        <p class="hero-subtitle">Verifikasi pendaftaran sekolah/universitas baru, perbarui profil, atau hapus data institusi.</p>
    </div>
</div>

<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-school-flag"></i> Daftar Institusi Terdaftar</h3>
    </div>

    @if($institutions->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-info-circle"></i>
            <span>Belum ada institusi terdaftar dalam database.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Nama Institusi</th>
                        <th>Email Admin</th>
                        <th>NPSN</th>
                        <th>Tipe</th>
                        <th>No. Telp</th>
                        <th>Status</th>
                        <th>Aksi Management</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($institutions as $inst)
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td><strong>{{ $inst->user->name }}</strong></td>
                            <td>{{ $inst->user->email }}</td>
                            <td>{{ $inst->npsn ?? '-' }}</td>
                            <td><span class="card-cat-badge">{{ ucfirst($inst->type) }}</span></td>
                            <td>{{ $inst->user->phone ?? '-' }}</td>
                            <td>
                                @if($inst->is_verified)
                                    <span class="card-rating-badge" style="background-color: #D1F5E4; color: #065F46;">
                                        <i class="fa-solid fa-circle-check"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="card-rating-badge" style="background-color: #FEF3C7; color: #92400E;">
                                        <i class="fa-solid fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$inst->is_verified)
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <form action="/admin/institutions/{{ $inst->id }}/verify" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #059669; color: #FFFFFF;">
                                                <i class="fa-solid fa-check"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="/admin/institutions/{{ $inst->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menolak pendaftaran institusi ini?');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B;">
                                                <i class="fa-solid fa-xmark"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <a href="/admin/institutions/{{ $inst->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form action="/admin/institutions/{{ $inst->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus institusi ini secara permanen dari database?');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B;">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
