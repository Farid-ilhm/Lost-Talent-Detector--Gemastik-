@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Akun Pengguna</h1>
        <p class="hero-subtitle">Pantau seluruh pengguna terdaftar, perbarui informasi profil, atau hapus akun pengguna dari sistem.</p>
    </div>
</div>

<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="section-title" style="margin: 0;"><i class="fa-solid fa-users"></i> Daftar Pengguna Terdaftar</h3>
    </div>

    <!-- Filter and Search Form -->
    <form action="/admin/users" method="GET" style="margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; background-color: #FAFAF8; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-subtle);">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau no. telp..." value="{{ request('search') }}" style="margin: 0; padding: 8px 12px; font-size: 0.9rem;">
        </div>
        <div style="width: 150px;">
            <select name="role" class="form-control" style="margin: 0; padding: 8px 12px; font-size: 0.9rem; font-weight: 600;">
                <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>Semua Role</option>
                <option value="institusi" {{ request('role') == 'institusi' ? 'selected' : '' }}>Institusi</option>
                <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                <option value="mahasiswa" {{ request('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="umum" {{ request('role') == 'umum' ? 'selected' : '' }}>Umum (Mandiri)</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn-primary-dark" style="padding: 8px 16px; font-size: 0.9rem; font-weight: 700;">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
        </div>
        @if(request()->has('search') || request()->has('role'))
            <div>
                <a href="/admin/users" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); padding: 8px 16px; font-size: 0.9rem; text-decoration: none; display: inline-block; border-radius: 12px; font-weight: 700;">
                    Reset
                </a>
            </div>
        @endif
    </form>

    @if($users->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-info-circle"></i>
            <span>Tidak ada pengguna yang cocok dengan kriteria pencarian.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Role</th>
                        <th>Detail Asosiasi</th>
                        <th>Tanggal Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $u->name }}</strong>
                                @if($u->id === Auth::id())
                                    <span class="card-rating-badge" style="background-color: #D1F5E4; color: #065F46; font-size: 0.7rem; padding: 2px 6px; margin-left: 4px;">Anda</span>
                                @endif
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone ?? '-' }}</td>
                            <td>
                                <span class="card-cat-badge" style="
                                    background-color: @if($u->role === 'admin') #FBE3E2 @elseif($u->role === 'institusi') #F9EBD7 @elseif($u->role === 'guru') #E2E4FC @else #D0F2E3 @endif;
                                    color: @if($u->role === 'admin') #991B1B @elseif($u->role === 'institusi') #92400E @elseif($u->role === 'guru') #3730A3 @else #065F46 @endif;
                                ">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem;">
                                @if($u->role === 'institusi' && $u->institution)
                                    NPSN: {{ $u->institution->npsn ?? '-' }} 
                                    @if($u->institution->is_verified)
                                        <span style="color: #059669; font-weight: 700;">(Ok)</span>
                                    @else
                                        <span style="color: #D97706; font-weight: 700;">(Pending)</span>
                                    @endif
                                @elseif($u->role === 'guru' && $u->teacher)
                                    Institusi: {{ $u->teacher->institution->user->name ?? 'Tidak Ada' }}
                                @elseif(in_array($u->role, ['siswa', 'mahasiswa', 'umum']) && $u->student)
                                    Institusi: {{ $u->student->institution->user->name ?? 'Mandiri' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $u->created_at ? $u->created_at->format('d-m-Y') : '-' }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a href="/admin/users/{{ $u->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    @if($u->id !== Auth::id())
                                        <form action="/admin/users/{{ $u->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini secara permanen dari database? Seluruh data yang berkaitan dengan user ini akan ikut dihapus.');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B; border: none; cursor: pointer;">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
