@extends('layouts.dashboard')
@section('title', 'Buat Closing Kasir')
@section('page-title', 'Buat Closing Kasir')

@push('styles')
<style>
.preview-card { display:none; }
.preview-card.show { display:block; }
</style>
@endpush

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <div class="card-title">📋 Generate Closing Kasir</div>
        <a href="{{ route('closing-kasir.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" id="tanggalInput" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Shift</label>
                <select id="shiftInput" class="form-control">
                    <option value="pagi">Pagi (07:00 - 13:59)</option>
                    <option value="siang">Siang (14:00 - 21:00)</option>
                </select>
            </div>
        </div>

        <button type="button" onclick="previewClosing()" class="btn btn-primary">
            🔍 Preview Data
        </button>

        <div id="previewAlert" style="display:none; margin-top:16px;" class="alert"></div>

        {{-- Preview Result --}}
        <div class="preview-card" id="previewCard" style="margin-top:20px;">
            <div class="table-wrap" style="border-radius:10px; overflow:hidden; border:1px solid var(--border);">
                <table>
                    <thead>
                        <tr>
                            <th>R/</th>
                            <th>HV</th>
                            <th>Pendapatan R/</th>
                            <th>Pendapatan HV</th>
                            <th>Total Pendapatan</th>
                            <th>Non Tunai</th>
                            <th>Total (Tunai)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="pJumlahResep" style="font-weight:700;">0</td>
                            <td id="pJumlahHv" style="font-weight:700;">0</td>
                            <td id="pPendapatanResep">0</td>
                            <td id="pPendapatanHv">0</td>
                            <td id="pTotalPendapatan" style="font-weight:700;">0</td>
                            <td id="pNonTunai">0</td>
                            <td id="pTotal" style="font-weight:800;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('closing-kasir.store') }}" style="margin-top:16px;">
                @csrf
                <input type="hidden" name="tanggal" id="formTanggal">
                <input type="hidden" name="shift" id="formShift">

                {{-- Akuntabilitas Kas --}}
                <div style="background:var(--bg-secondary, #f8f9fa); border-radius:10px; padding:16px; margin-bottom:16px;">
                    <h4 style="margin:0 0 12px 0; font-size:14px; font-weight:700;">💰 Hitung Kas Fisik</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kas Awal (uang di laci saat mulai shift)</label>
                            <input type="text" name="modal_awal" id="inputModalAwal" class="form-control rupiah-input" placeholder="Rp 0" value="0">
                            <small style="color:var(--text-muted);">Uang yang sudah ada di laci sebelum shift dimulai</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Setoran ke Pemilik (jika ada)</label>
                            <input type="text" name="setoran" id="inputSetoran" class="form-control rupiah-input" placeholder="Rp 0" value="0" oninput="hitungSelisih()">
                            <small style="color:var(--text-muted);">Uang yang diambil pemilik selama shift ini</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Uang Fisik di Laci Sekarang *</label>
                            <input type="text" name="uang_fisik" id="inputUangFisik" class="form-control rupiah-input" placeholder="Hitung uang di laci" oninput="hitungSelisih()">
                            <small style="color:var(--text-muted);">Hitung semua uang tunai di laci saat ini</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Seharusnya</label>
                            <input type="text" id="displaySeharusnya" class="form-control" disabled value="Rp 0">
                            <small style="color:var(--text-muted);">Kas Awal + Tunai Masuk - Setoran</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" style="font-weight:700;">Selisih</label>
                            <input type="text" id="displaySelisih" class="form-control" disabled value="Rp 0" style="font-weight:700; font-size:16px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan (wajib jika ada selisih)</label>
                        <input type="text" name="keterangan" id="inputKeterangan" class="form-control" placeholder="Jelaskan jika ada selisih...">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" onclick="return confirmClosing()">
                    💾 Simpan Closing
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let previewTotal = 0; // Total tunai dari preview

function previewClosing() {
    const tanggal = document.getElementById('tanggalInput').value;
    const shift = document.getElementById('shiftInput').value;
    const alert = document.getElementById('previewAlert');
    const card = document.getElementById('previewCard');

    if (!tanggal) {
        alert.className = 'alert alert-danger';
        alert.textContent = '⚠️ Pilih tanggal terlebih dahulu.';
        alert.style.display = 'block';
        card.classList.remove('show');
        return;
    }

    fetch('{{ route("closing-kasir.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ tanggal, shift }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.exists) {
            alert.className = 'alert alert-danger';
            alert.textContent = '⚠️ ' + res.message;
            alert.style.display = 'block';
            card.classList.remove('show');
            return;
        }

        const d = res.data;
        document.getElementById('pJumlahResep').textContent = d.jumlah_resep;
        document.getElementById('pJumlahHv').textContent = d.jumlah_hv;
        document.getElementById('pPendapatanResep').textContent = formatRp(d.pendapatan_resep);
        document.getElementById('pPendapatanHv').textContent = formatRp(d.pendapatan_hv);
        document.getElementById('pTotalPendapatan').textContent = formatRp(d.total_pendapatan);
        document.getElementById('pNonTunai').textContent = formatRp(d.non_tunai);
        document.getElementById('pTotal').textContent = formatRp(d.total);

        // Simpan total tunai untuk hitung selisih
        previewTotal = parseFloat(d.total) || 0;
        hitungSelisih();

        document.getElementById('formTanggal').value = tanggal;
        document.getElementById('formShift').value = shift;

        alert.className = 'alert alert-success';
        alert.textContent = '✓ Data ditemukan. Periksa dan simpan.';
        alert.style.display = 'block';
        card.classList.add('show');
    })
    .catch(err => {
        alert.className = 'alert alert-danger';
        alert.textContent = '⚠️ Terjadi kesalahan.';
        alert.style.display = 'block';
        card.classList.remove('show');
    });
}

function hitungSelisih() {
    const modalAwal = parseRupiah(document.getElementById('inputModalAwal').value);
    const setoran = parseRupiah(document.getElementById('inputSetoran').value);
    const uangFisik = parseRupiah(document.getElementById('inputUangFisik').value);

    // Seharusnya = Kas Awal + Pendapatan Tunai - Setoran
    const seharusnya = modalAwal + previewTotal - setoran;

    document.getElementById('displaySeharusnya').value = 'Rp ' + formatRp(seharusnya);

    if (uangFisik > 0 || document.getElementById('inputUangFisik').value.trim() !== '') {
        const selisih = uangFisik - seharusnya;
        const el = document.getElementById('displaySelisih');
        if (selisih === 0) {
            el.value = 'Rp 0 ✓ Cocok';
            el.style.color = '#28a745';
        } else {
            el.value = (selisih > 0 ? '+' : '-') + ' Rp ' + formatRp(Math.abs(selisih));
            el.style.color = selisih < 0 ? '#dc3545' : '#28a745';
        }
    } else {
        document.getElementById('displaySelisih').value = '-';
        document.getElementById('displaySelisih').style.color = 'inherit';
    }
}

function confirmClosing() {
    const uangFisik = parseRupiah(document.getElementById('inputUangFisik').value);
    const modalAwal = parseRupiah(document.getElementById('inputModalAwal').value);
    const seharusnya = previewTotal + modalAwal;
    const selisih = uangFisik - seharusnya;
    const keterangan = document.getElementById('inputKeterangan').value.trim();

    if (uangFisik === 0 && document.getElementById('inputUangFisik').value.trim() === '') {
        return confirm('Uang fisik belum diisi. Simpan tanpa verifikasi kas?');
    }

    if (selisih !== 0 && !keterangan) {
        alert('⚠️ Ada selisih kas! Wajib isi keterangan sebelum menyimpan.');
        document.getElementById('inputKeterangan').focus();
        return false;
    }

    if (selisih < 0) {
        return confirm('⚠️ PERHATIAN: Kas KURANG Rp ' + formatRp(Math.abs(selisih)) + '!\n\nKeterangan: ' + keterangan + '\n\nSimpan closing ini?');
    }

    return confirm('Simpan closing ini?');
}

function parseRupiah(str) {
    if (!str) return 0;
    return parseInt(str.replace(/[^\d]/g, '')) || 0;
}

function formatRp(num) {
    return Math.round(num).toLocaleString('id-ID');
}

// Auto-format rupiah inputs
document.querySelectorAll('.rupiah-input').forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value.replace(/[^\d]/g, '');
        if (val) {
            this.value = 'Rp ' + parseInt(val).toLocaleString('id-ID');
        } else {
            this.value = '';
        }
        hitungSelisih();
    });
});
</script>
@endpush
