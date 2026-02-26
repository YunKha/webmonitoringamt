@extends('admin.layouts.app')

@section('page-title', 'Buat Tiket Pengantaran')

@section('content')
    <div class="card" style="max-width:700px;">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:8px;"></i> Form Tiket Baru</h3>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tickets.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                    <div class="form-group">
                        <label for="lo_number">Nomor LO *</label>
                        <input type="text" id="lo_number" name="lo_number" class="form-control @error('lo_number') is-invalid @enderror"
                               value="{{ old('lo_number') }}" placeholder="Contoh: LO-2024-001" required>
                        @error('lo_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="spbu_number">Nomor SPBU *</label>
                        <input type="text" id="spbu_number" name="spbu_number" class="form-control @error('spbu_number') is-invalid @enderror"
                               value="{{ old('spbu_number') }}" placeholder="Contoh: 34.401.01" required>
                        @error('spbu_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="ship_to">Nomor Ship To *</label>
                        <input type="text" id="ship_to" name="ship_to" class="form-control @error('ship_to') is-invalid @enderror"
                               value="{{ old('ship_to') }}" placeholder="Contoh: 1234567" required>
                        @error('ship_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="quantity">Jumlah KL/DO (Liter) *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" placeholder="Contoh: 8000" step="0.01" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="product_type">Jenis Produk *</label>
                        <select id="product_type" name="product_type" class="form-control @error('product_type') is-invalid @enderror" required>
                            <option value="">Pilih Produk</option>
                            <option value="Pertalite" {{ old('product_type') == 'Pertalite' ? 'selected' : '' }}>Pertalite</option>
                            <option value="Pertamax" {{ old('product_type') == 'Pertamax' ? 'selected' : '' }}>Pertamax</option>
                            <option value="Pertamax Turbo" {{ old('product_type') == 'Pertamax Turbo' ? 'selected' : '' }}>Pertamax Turbo</option>
                            <option value="Pertamina Dex" {{ old('product_type') == 'Pertamina Dex' ? 'selected' : '' }}>Pertamina Dex</option>
                            <option value="Solar" {{ old('product_type') == 'Solar' ? 'selected' : '' }}>Solar</option>
                            <option value="Bio Solar" {{ old('product_type') == 'Bio Solar' ? 'selected' : '' }}>Bio Solar</option>
                        </select>
                        @error('product_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="distance_km">Jarak Tempuh (km) *</label>
                        <input type="number" id="distance_km" name="distance_km" class="form-control @error('distance_km') is-invalid @enderror"
                               value="{{ old('distance_km') }}" placeholder="Contoh: 32" step="0.01" required>
                        @error('distance_km')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                            <label for="driver_name">Nama Sopir *</label>
                            <div style="position:relative;">
                                <input type="text" id="driver_name" name="driver_name"
                                       class="form-control @error('driver_name') is-invalid @enderror"
                                       value="{{ old('driver_name') }}"
                                       placeholder="Ketik nama sopir..."
                                       autocomplete="off"
                                       required>
                                <ul id="driver_name_suggestions" class="autocomplete-list"></ul>
                            </div>
                            @error('driver_name')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                            @enderror
                    </div>

                        {{-- NAMA KARNET dengan autocomplete --}}
                        <div class="form-group">
                            <label for="karnet_number">Nama Karnet *</label>
                            <div style="position:relative;">
                                <input type="text" id="karnet_number" name="karnet_number"
                                       class="form-control @error('karnet_number') is-invalid @enderror"
                                       value="{{ old('karnet_number') }}"
                                       placeholder="Ketik nama karnet..."
                                       autocomplete="off"
                                       required>
                                <ul id="karnet_number_suggestions" class="autocomplete-list"></ul>
                            </div>
                            @error('karnet_number')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                            @enderror
                        </div>
                    
                </div>

                {{-- ===== SECTION DATA SOPIR ===== --}}
                
                        {{-- NAMA SOPIR dengan autocomplete --}}
                        
                {{-- ===== END SECTION DATA SOPIR ===== --}}

                <div style="display:flex;gap:12px;margin-top:20px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Tiket</button>
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

<style>
.autocomplete-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 6px 6px;
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.autocomplete-list li {
    padding: 9px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
}
.autocomplete-list li:last-child { border-bottom: none; }
.autocomplete-list li:hover,
.autocomplete-list li.active {
    background: #EFF6FF;
    color: var(--primary, #2563eb);
}
.autocomplete-list li span.highlight {
    font-weight: 700;
    color: var(--primary, #2563eb);
}
</style>

<script>
// Data sopir dari Laravel — di-encode sebagai JSON array nama
const driverNames = @json($drivers->pluck('name'));

/**
 * Inisialisasi autocomplete pada sebuah input
 * @param {string} inputId   - id input
 * @param {string} listId    - id <ul> saran
 */
function initAutocomplete(inputId, listId) {
    const input = document.getElementById(inputId);
    const list  = document.getElementById(listId);
    if (!input || !list) return;

    let activeIndex = -1;

    input.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        list.innerHTML = '';
        activeIndex = -1;

        if (!query) { list.style.display = 'none'; return; }

        // Filter nama yang mengandung karakter yang diketik
        const matches = driverNames.filter(name =>
            name.toLowerCase().includes(query)
        );

        if (matches.length === 0) { list.style.display = 'none'; return; }

        matches.forEach(function (name) {
            const li = document.createElement('li');

            // Bold bagian yang cocok
            const idx   = name.toLowerCase().indexOf(query);
            const before = name.substring(0, idx);
            const match  = name.substring(idx, idx + query.length);
            const after  = name.substring(idx + query.length);
            li.innerHTML = before + '<span class="highlight">' + match + '</span>' + after;

            li.addEventListener('mousedown', function (e) {
                e.preventDefault(); // cegah blur sebelum klik
                input.value = name;
                list.style.display = 'none';
            });

            list.appendChild(li);
        });

        list.style.display = 'block';
    });

    // Navigasi keyboard ↑ ↓ Enter Escape
    input.addEventListener('keydown', function (e) {
        const items = list.querySelectorAll('li');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            updateActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, -1);
            updateActive(items);
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            input.value = items[activeIndex].textContent;
            list.style.display = 'none';
            activeIndex = -1;
        } else if (e.key === 'Escape') {
            list.style.display = 'none';
            activeIndex = -1;
        }
    });

    function updateActive(items) {
        items.forEach((li, i) => {
            li.classList.toggle('active', i === activeIndex);
        });
    }

    // Tutup saran saat klik di luar
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initAutocomplete('driver_name',   'driver_name_suggestions');
    initAutocomplete('karnet_number', 'karnet_number_suggestions');
});
</script>
@endsection