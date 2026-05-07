<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Titik Patroli (Check Point)</h1>
      <button @click="openCreateModal" class="btn btn-primary">+ Tambah Titik</button>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
      <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Lokasi Patroli</label>
          <select v-model="filterLocationID" class="input">
            <option value="">Semua Lokasi</option>
            <option v-for="loc in lokasiList" :key="loc.id" :value="loc.id">{{ loc.NamaArea }}</option>
          </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button @click="fetchItems" class="btn btn-secondary">Cari</button>
          <button @click="exportQRSemua" class="btn btn-secondary" :disabled="items.length === 0" title="Cetak semua QR Code yang tampil">
            &#128247; Export QR Semua
          </button>
        </div>
        <div style="flex: 1; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Cari Kode / Nama</label>
          <input v-model="searchQuery" class="input" placeholder="Ketik untuk mencari..." />
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card table-container" style="position: relative; min-height: 200px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>Kode Check Point</th>
                <th>Nama Check Point</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="paginatedItems.length === 0">
                <td colspan="5" style="text-align: center; color: #888; padding: 2rem;">
                  Tidak ada data titik patroli.
                </td>
              </tr>
              <tr v-for="item in paginatedItems" :key="item.KodeCheckPoint">
                <td><code style="background:#f0f4f8; padding:2px 6px; border-radius:4px; font-size:0.85rem;">{{ item.KodeCheckPoint }}</code></td>
                <td>{{ item.NamaCheckPoint }}</td>
                <td>{{ item.lokasi?.NamaArea || '-' }}</td>
                <td>{{ item.Keterangan || '-' }}</td>
                <td>
                  <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button @click="showQR(item)" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.85rem;" title="Lihat QR Code">
                      &#128247; QR
                    </button>
                    <button @click="editItem(item.KodeCheckPoint)" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Edit</button>
                    <button @click="deleteItem(item.KodeCheckPoint, item.NamaCheckPoint)" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-footer" style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>Show</span>
            <select v-model="itemsPerPage" class="input" style="width: auto;">
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
            <span>per halaman — Total: {{ filteredItems.length }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary">&#8249;</button>
            <span>{{ currentPage }} / {{ totalPages || 1 }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++" class="btn btn-secondary">&#8250;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL CREATE / EDIT ===== -->
    <div
      v-if="showModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;"
    >
      <div class="card" style="width: 100%; max-width: 560px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit Titik Patroli' : 'Tambah Titik Patroli' }}</h2>
          <button @click="showModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Kode Check Point -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Kode Check Point <span style="color:#e53e3e;">*</span>
          </label>
          <input
            v-model="form.KodeCheckPoint"
            class="input"
            placeholder="Contoh: CP-001, GATE-A..."
            :readonly="isEditing"
            :style="isEditing ? 'background:#f7fafc; cursor:not-allowed; color:#718096;' : ''"
          />
          <p v-if="isEditing" style="font-size: 0.75rem; color: #718096; margin: 0.25rem 0 0;">Kode tidak dapat diubah setelah disimpan.</p>
          <p v-else style="font-size: 0.75rem; color: #718096; margin: 0.25rem 0 0;">Kode ini akan di-encode ke dalam QR Code.</p>
        </div>

        <!-- Nama Check Point -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Nama Check Point <span style="color:#e53e3e;">*</span>
          </label>
          <input v-model="form.NamaCheckPoint" class="input" placeholder="Nama titik patroli..." />
        </div>

        <!-- Lokasi -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Lokasi Patroli <span style="color:#e53e3e;">*</span>
          </label>
          <select v-model="form.LocationID" class="input">
            <option value="">Pilih Lokasi...</option>
            <option v-for="loc in lokasiList" :key="loc.id" :value="loc.id">{{ loc.NamaArea }}</option>
          </select>
        </div>

        <!-- Keterangan -->
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Keterangan</label>
          <input v-model="form.Keterangan" class="input" placeholder="Keterangan tambahan..." />
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
          <button @click="showModal = false" class="btn btn-secondary">Batal</button>
          <button @click="saveItem" class="btn btn-primary" :disabled="isSaving">
            {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ===== MODAL QR CODE ===== -->
    <div
      v-if="qrModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;"
    >
      <div class="card" style="width: 100%; max-width: 380px; text-align: center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
          <h2 class="page-title" style="margin: 0; font-size: 1rem;">QR Code — Titik Patroli</h2>
          <button @click="qrModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- QR Image -->
        <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
          <div ref="qrContainer" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; display: inline-block; background: #fff;"></div>
        </div>

        <!-- Info -->
        <p style="font-weight: 600; font-size: 0.95rem; margin: 0 0 0.25rem;">{{ qrItem?.KodeCheckPoint }}</p>
        <p style="color: #718096; font-size: 0.85rem; margin: 0 0 1.25rem;">{{ qrItem?.NamaCheckPoint }}</p>
        <p style="color: #a0aec0; font-size: 0.75rem; margin: 0 0 1rem;">{{ qrItem?.lokasi?.NamaArea }}</p>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
          <button @click="downloadQR" class="btn btn-primary">&#11015; Download PNG</button>
          <button @click="qrModal = false" class="btn btn-secondary">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import Swal from 'sweetalert2';

const QR_SIZE = 220;

export default {
  name: 'TitikPatroli',
  components: { LoadingSpinner },

  data() {
    return {
      items: [],
      lokasiList: [],
      isLoading: false,
      isSaving: false,
      showModal: false,
      isEditing: false,
      searchQuery: '',
      filterLocationID: '',
      currentPage: 1,
      itemsPerPage: 10,
      form: this.defaultForm(),

      // QR
      qrModal: false,
      qrItem: null,
    };
  },

  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(
        (i) =>
          i.KodeCheckPoint?.toLowerCase().includes(q) ||
          i.NamaCheckPoint?.toLowerCase().includes(q),
      );
    },
    totalPages() {
      return Math.ceil(this.filteredItems.length / this.itemsPerPage);
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredItems.slice(start, start + this.itemsPerPage);
    },
  },

  watch: {
    searchQuery() {
      this.currentPage = 1;
    },
  },

  mounted() {
    this.fetchLokasi();
    this.fetchItems();
  },

  methods: {
    defaultForm() {
      return { KodeCheckPoint: '', NamaCheckPoint: '', LocationID: '', Keterangan: '' };
    },

    async fetchLokasi() {
      try {
        const res = await axios.get('/lokasi-patroli');
        this.lokasiList = res.data.data;
      } catch (e) {
        console.error('Gagal load lokasi:', e);
      }
    },

    async fetchItems() {
      this.isLoading = true;
      this.currentPage = 1;
      try {
        const params = {};
        if (this.filterLocationID) params.LocationID = this.filterLocationID;
        const res = await axios.get('/titik-patroli', { params });
        this.items = res.data.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.isLoading = false;
      }
    },

    openCreateModal() {
      this.form = this.defaultForm();
      this.isEditing = false;
      this.showModal = true;
    },

    async editItem(kode) {
      Swal.fire({
        title: 'Memuat data...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });
      try {
        const res = await axios.get(`/titik-patroli/${encodeURIComponent(kode)}`);
        const d = res.data.data;
        this.form = {
          KodeCheckPoint: d.KodeCheckPoint,
          NamaCheckPoint: d.NamaCheckPoint,
          LocationID:     d.LocationID,
          Keterangan:     d.Keterangan || '',
        };
        this.isEditing = true;
        this.showModal = true;
        Swal.close();
      } catch (e) {
        Swal.fire('Error', 'Gagal memuat data.', 'error');
      }
    },

    async saveItem() {
      if (!this.form.KodeCheckPoint.trim()) {
        Swal.fire('Perhatian', 'Kode Check Point tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.NamaCheckPoint.trim()) {
        Swal.fire('Perhatian', 'Nama Check Point tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.LocationID) {
        Swal.fire('Perhatian', 'Lokasi Patroli harus dipilih.', 'warning');
        return;
      }

      this.isSaving = true;
      try {
        if (this.isEditing) {
          await axios.put(`/titik-patroli/${encodeURIComponent(this.form.KodeCheckPoint)}`, this.form);
        } else {
          await axios.post('/titik-patroli', this.form);
        }
        this.showModal = false;
        await this.fetchItems();
        Swal.fire('Berhasil!', 'Data titik patroli berhasil disimpan.', 'success');
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Terjadi kesalahan saat menyimpan.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async deleteItem(kode, nama) {
      const result = await Swal.fire({
        title: 'Yakin hapus titik patroli ini?',
        html: `Kode: <strong>${kode}</strong><br>Nama: <strong>${nama}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e53e3e',
      });
      if (!result.isConfirmed) return;

      Swal.fire({
        title: 'Menghapus...',
        text: 'Mohon tunggu sebentar.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        await axios.delete(`/titik-patroli/${encodeURIComponent(kode)}`);
        await this.fetchItems();
        Swal.fire('Dihapus!', 'Titik patroli berhasil dihapus.', 'success');
      } catch (e) {
        Swal.fire('Gagal', e.response?.data?.message || 'Gagal menghapus data.', 'error');
      }
    },

    // ===== QR CODE =====
    showQR(item) {
      this.qrItem  = item;
      this.qrModal = true;
      this.$nextTick(() => this.renderQR(item.KodeCheckPoint, this.$refs.qrContainer));
    },

    renderQR(text, container) {
      if (!container) return;
      container.innerHTML = '';
      this.loadQRLib().then(() => {
        new window.QRCode(container, {
          text,
          width:      QR_SIZE,
          height:     QR_SIZE,
          colorDark:  '#1a202c',
          colorLight: '#ffffff',
          correctLevel: window.QRCode.CorrectLevel.H,
        });
      });
    },

    loadQRLib() {
      return new Promise((resolve) => {
        if (window.QRCode) { resolve(); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js';
        s.onload = resolve;
        document.head.appendChild(s);
      });
    },

    downloadQR() {
      const canvas = this.$refs.qrContainer?.querySelector('canvas');
      if (!canvas) return;
      const link    = document.createElement('a');
      link.download = `QR-${this.qrItem.KodeCheckPoint}.png`;
      link.href     = canvas.toDataURL('image/png');
      link.click();
    },

    // Export semua QR Code yang sedang tampil ke jendela baru untuk dicetak
    exportQRSemua() {
      const data = this.filteredItems;
      if (!data.length) return;

      const rows = data.map((item) => `
        <div class="qr-item">
          <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(item.KodeCheckPoint)}&margin=10"
            alt="${item.KodeCheckPoint}"
            width="180" height="180"
          />
          <p class="kode">${item.KodeCheckPoint}</p>
          <p class="nama">${item.NamaCheckPoint}</p>
          <p class="lokasi">${item.lokasi?.NamaArea || ''}</p>
        </div>`).join('');

      const lokasiLabel = this.filterLocationID
        ? this.lokasiList.find((l) => l.id == this.filterLocationID)?.NamaArea || ''
        : 'Semua Lokasi';

      const html = `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>QR Code Titik Patroli — ${lokasiLabel}</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    h2   { margin-bottom: 4px; }
    p.sub{ color: #666; font-size: 13px; margin-bottom: 20px; }
    .grid{ display: flex; flex-wrap: wrap; gap: 16px; }
    .qr-item { border: 1px solid #ddd; border-radius: 8px; padding: 12px; text-align: center; width: 200px; }
    .qr-item img { display: block; margin: 0 auto 8px; }
    .kode  { font-weight: 700; font-size: 13px; margin: 0; }
    .nama  { font-size: 12px; color: #333; margin: 2px 0; }
    .lokasi{ font-size: 11px; color: #888; margin: 0; }
    @media print { body { padding: 8px; } }
  </style>
</head>
<body>
  <h2>QR Code Titik Patroli</h2>
  <p class="sub">Lokasi: ${lokasiLabel} &nbsp;|&nbsp; Total: ${data.length} titik</p>
  <div class="grid">${rows}</div>
  <script>window.onload = function(){ window.print(); }<\/script>
</body>
</html>`;

      const win = window.open('', '_blank');
      if (win) {
        win.document.write(html);
        win.document.close();
      }
    },
  },
};
</script>