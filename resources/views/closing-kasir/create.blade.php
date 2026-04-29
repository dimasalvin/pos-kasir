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
                <button type="submit" class="btn btn-primary" onclick="return confirm('Simpan closing ini?')">
                    💾 Simpan Closing
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

function formatRp(num) {
    return Math.round(num).toLocaleString('id-ID');
}
</script>
@endpush
