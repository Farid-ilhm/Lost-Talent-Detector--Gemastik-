@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Master Kompetisi</h1>
        <p class="hero-subtitle">Atur basis data master perlombaan nasional/internasional yang direkomendasikan sistem AI ke siswa.</p>
    </div>
</div>

<!-- Kelola Master Data Lomba -->
<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
        <h3 class="section-title" style="margin: 0;"><i class="fa-solid fa-trophy"></i> Master Kompetisi Nasional/Internasional</h3>
        <button type="button" id="btn-delete-selected" class="btn-primary-dark" style="background-color: #FBE3E2; color: #991B1B; padding: 8px 16px; font-weight: 700; border-radius: 12px; display: none; align-items: center; gap: 8px; border: none; cursor: pointer; font-size: 0.85rem; transition: all 0.2s ease;">
            <i class="fa-solid fa-trash"></i> Hapus Terpilih
        </button>
    </div>

    @if($competitions->isEmpty())
        <div class="alert-custom alert-warning" style="margin-bottom: 24px;">
            <i class="fa-solid fa-info-circle"></i>
            <span>Belum ada data kompetisi master di database.</span>
        </div>
    @else
        <div class="table-responsive" style="margin-bottom: 24px; overflow-x: auto;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center; padding: 12px 8px;">
                            <input type="checkbox" id="select-all-comp" style="transform: scale(1.3); cursor: pointer; margin: 0;">
                        </th>
                        <th>Judul Kompetisi</th>
                        <th>Kategori</th>
                        <th>Penyelenggara</th>
                        <th>Batas Pendaftaran</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competitions as $comp)
                        <tr>
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" value="{{ $comp->id }}" class="comp-checkbox" style="transform: scale(1.3); cursor: pointer; margin: 0;">
                            </td>
                            <td><strong>{{ $comp->title }}</strong></td>
                            <td><span class="card-cat-badge">{{ ucfirst($comp->category) }}</span></td>
                            <td>{{ $comp->organizer ?? '-' }}</td>
                            <td>{{ $comp->registration_deadline ? $comp->registration_deadline->format('d-m-Y') : '-' }}</td>
                            <td>{{ $comp->description ?? '-' }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a href="/admin/competitions/{{ $comp->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <form action="/admin/competitions/{{ $comp->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus kompetisi ini?');">
                                        @csrf
                                        <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B; border: none; cursor: pointer;">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Form Tambah Lomba -->
    <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
        <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 16px;"><i class="fa-solid fa-circle-plus" style="color: #059669;"></i> Tambah Master Kompetisi Baru</h4>
        <form action="/admin/competitions" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="title" class="form-label">Judul Kompetisi:</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div>
                    <label for="category" class="form-label">Kategori Lomba:</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="teknologi">Teknologi / IT</option>
                        <option value="sains">Sains / Matematika</option>
                        <option value="seni">Seni & Desain</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label for="organizer" class="form-label">Penyelenggara / Institusi:</label>
                    <input type="text" id="organizer" name="organizer" class="form-control" placeholder="contoh: Puspresnas">
                </div>
                <div>
                    <label for="registration_deadline" class="form-label">Batas Pendaftaran:</label>
                    <input type="date" id="registration_deadline" name="registration_deadline" class="form-control">
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label for="description" class="form-label">Deskripsi Lomba:</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Deskripsikan perlombaan..."></textarea>
            </div>
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-plus"></i> Tambah Master Kompetisi
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-comp');
    const checkboxes = document.querySelectorAll('.comp-checkbox');
    const deleteSelectedBtn = document.getElementById('btn-delete-selected');

    function toggleDeleteButtonVisibility() {
        const checkedCount = document.querySelectorAll('.comp-checkbox:checked').length;
        if (checkedCount > 0) {
            deleteSelectedBtn.style.display = 'inline-flex';
            deleteSelectedBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Hapus Terpilih (' + checkedCount + ')';
        } else {
            deleteSelectedBtn.style.display = 'none';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCheckbox.checked;
            });
            toggleDeleteButtonVisibility();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            if (!cb.checked) {
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
            }
            toggleDeleteButtonVisibility();
        });
    });

    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const checkedCheckboxes = document.querySelectorAll('.comp-checkbox:checked');
            if (checkedCheckboxes.length === 0) return;
            
            if (confirm('Yakin ingin menghapus ' + checkedCheckboxes.length + ' kompetisi yang terpilih secara massal?')) {
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = '/admin/competitions/delete-multiple';
                
                // Add CSRF Token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                tempForm.appendChild(csrfInput);
                
                // Add selected IDs
                checkedCheckboxes.forEach(function(cb) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'comp_ids[]';
                    idInput.value = cb.value;
                    tempForm.appendChild(idInput);
                });
                
                document.body.appendChild(tempForm);
                tempForm.submit();
            }
        });
    }
});
</script>
@endsection
