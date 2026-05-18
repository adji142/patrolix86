<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Review Daily Activity</h1>
      <div style="display: flex; gap: 0.75rem;">
        <button @click="exportExcel" class="btn btn-secondary" :disabled="filteredItems.length === 0">
          &#128190; Export Excel
        </button>
        <button @click="openCreate" class="btn btn-primary">+ Tambah</button>
      </div>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom: 1.25rem;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div>
          <label style="display: block; font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">Tgl Awal</label>
          <input v-model="filter.tgl_awal" type="date" class="input" style="width: 160px;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">Tgl Akhir</label>
          <input v-model="filter.tgl_akhir" type="date" class="input" style="width: 160px;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">Lokasi</label>
          <select v-model="filter.location_id" class="input" style="width: 200px;" @change="onFilterLokasiChange">
            <option value="">Semua Lokasi</option>
            <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
          </select>
        </div>
        <div>
          <label style="display: block; font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">Security</label>
          <select v-model="filter.kode_karyawan" class="input" style="width: 200px;">
            <option value="">Semua Security</option>
            <option v-for="s in filteredSecurityList" :key="s.NIK" :value="s.NIK">{{ s.NamaSecurity }}</option>
          </select>
        </div>
        <button @click="fetchData" class="btn btn-primary" :disabled="isLoading">
          {{ isLoading ? 'Memuat...' : 'Tampilkan' }}
        </button>
      </div>
    </div>

    <!-- Search + count -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
      <div style="font-size: 0.85rem; color: #6b7280;">
        <span v-if="hasFetched">{{ filteredItems.length }} record</span>
      </div>
      <input v-model="searchQuery" placeholder="Cari nama, deskripsi, lokasi..." class="input" style="width: 320px;" />
    </div>

    <!-- Tabel -->
    <div class="card table-container" style="position: relative; min-height: 200px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <div v-if="hasFetched && items.length === 0" style="text-align: center; padding: 3rem; color: #9ca3af;">
          Tidak ada data untuk periode dan filter yang dipilih.
        </div>
        <div v-else-if="!hasFetched" style="text-align: center; padding: 3rem; color: #9ca3af;">
          Pilih periode lalu klik Tampilkan.
        </div>
        <div v-else style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th style="white-space: nowrap;">Tanggal &amp; Waktu</th>
                <th style="white-space: nowrap;">NIK</th>
                <th style="white-space: nowrap;">Nama Security</th>
                <th>Deskripsi Aktivitas</th>
                <th>Lokasi</th>
                <th style="white-space: nowrap;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in paginatedItems" :key="item.id">
                <td>{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="white-space: nowrap;">{{ formatDatetime(item.Tanggal) }}</td>
                <td style="font-family: monospace; font-size: 0.8rem;">{{ item.KodeKaryawan }}</td>
                <td>{{ item.NamaKaryawan || '-' }}</td>
                <td style="max-width: 260px; word-break: break-word;">{{ item.DeskripsiAktifitas }}</td>
                <td style="white-space: nowrap;">{{ item.NamaLokasi || '-' }}</td>
                <td>
                  <div style="display: flex; gap: 0.4rem; flex-wrap: nowrap;">
                    <button v-if="item.Gambar1 || item.Gambar2 || item.Gambar3" @click="openPhoto(item)" class="btn btn-secondary" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">&#128247;</button>
                    <button @click="openEdit(item)" class="btn btn-secondary" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">Edit</button>
                    <button @click="deleteItem(item)" class="btn btn-danger" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="filteredItems.length > 0" class="pagination-footer" style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #6b7280;">
            <span>Tampilkan</span>
            <select v-model="itemsPerPage" class="input" style="width: 70px; padding: 0.25rem 0.5rem;">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <span>per halaman</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary" style="padding: 0.25rem 0.75rem;">&#8592;</button>
            <span style="font-size: 0.85rem; color: #374151;">Hal {{ currentPage }} / {{ totalPages }}</span>
            <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn btn-secondary" style="padding: 0.25rem 0.75rem;">&#8594;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Foto -->
    <div v-if="showPhotoModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="showPhotoModal = false">
      <div class="card" style="width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
          <div>
            <div style="font-weight: 700; font-size: 1rem; color: #111827;">{{ photoItem.NamaKaryawan || photoItem.KodeKaryawan }}</div>
            <div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">{{ formatDatetime(photoItem.Tanggal) }}</div>
          </div>
          <button @click="showPhotoModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
        </div>

        <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem; margin-bottom: 1.25rem;">
          <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">Deskripsi Aktivitas</div>
          <div style="font-size: 0.875rem; color: #374151; line-height: 1.5;">{{ photoItem.DeskripsiAktifitas }}</div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.25rem;">
          <div v-if="photoItem.Gambar1Url">
            <div style="font-size: 0.7rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; text-align: center;">Foto 1</div>
            <img :src="photoItem.Gambar1Url" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; cursor: pointer;"
              @click="openLightbox(photoItem.Gambar1Url)" @error="(e) => e.target.style.display='none'" />
          </div>
          <div v-if="photoItem.Gambar2Url">
            <div style="font-size: 0.7rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; text-align: center;">Foto 2</div>
            <img :src="photoItem.Gambar2Url" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; cursor: pointer;"
              @click="openLightbox(photoItem.Gambar2Url)" @error="(e) => e.target.style.display='none'" />
          </div>
          <div v-if="photoItem.Gambar3Url">
            <div style="font-size: 0.7rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; text-align: center;">Foto 3</div>
            <img :src="photoItem.Gambar3Url" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; cursor: pointer;"
              @click="openLightbox(photoItem.Gambar3Url)" @error="(e) => e.target.style.display='none'" />
          </div>
        </div>
        <div style="font-size: 0.75rem; color: #9ca3af; text-align: center;">Klik foto untuk memperbesar</div>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightboxUrl" style="position: fixed; inset: 0; background: rgba(0,0,0,0.9); display: flex; align-items: center; justify-content: center; z-index: 2000; cursor: zoom-out;" @click="lightboxUrl = null">
      <img :src="lightboxUrl" style="max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px;" />
    </div>

    <!-- Modal Form CRUD -->
    <div v-if="showFormModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="showFormModal = false">
      <div class="card" style="width: 100%; max-width: 660px; max-height: 92vh; overflow-y: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit Daily Activity' : 'Tambah Daily Activity' }}</h2>
          <button @click="showFormModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
        </div>

        <!-- Row: Tanggal + Waktu + Lokasi -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Tanggal &amp; Waktu <span style="color:#ef4444;">*</span></label>
            <input v-model="form.Tanggal" type="datetime-local" class="input" />
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Lokasi <span style="color:#ef4444;">*</span></label>
            <select v-model="form.LocationID" class="input" @change="onFormLokasiChange">
              <option value="">- Pilih Lokasi -</option>
              <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
            </select>
          </div>
        </div>

        <!-- Row: Security -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Security <span style="color:#ef4444;">*</span></label>
            <select v-model="form.KodeKaryawan" class="input" @change="onSecurityChange">
              <option value="">- Pilih Security -</option>
              <option v-for="s in formSecurityList" :key="s.NIK" :value="s.NIK">{{ s.NamaSecurity }}</option>
            </select>
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Nama Security</label>
            <input v-model="form.NamaKaryawan" type="text" class="input" placeholder="Terisi otomatis..." />
          </div>
        </div>

        <!-- Deskripsi -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Deskripsi Aktivitas <span style="color:#ef4444;">*</span></label>
          <textarea v-model="form.DeskripsiAktifitas" class="input" rows="3" placeholder="Tuliskan deskripsi aktivitas..." style="resize: vertical;"></textarea>
        </div>

        <!-- 3 Foto -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.5rem;">
          <div v-for="n in [1, 2, 3]" :key="n">
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Foto {{ n }}</label>
            <input type="file" accept="image/*" class="input" style="padding: 0.3rem; font-size: 0.75rem;" @change="onImageChange(n, $event)" />
            <div v-if="previews[n]" style="margin-top: 0.4rem;">
              <img :src="previews[n]" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" />
            </div>
            <div v-else-if="isEditing && form['Gambar' + n + 'Url']" style="margin-top: 0.4rem;">
              <img :src="form['Gambar' + n + 'Url']" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;"
                @error="(e) => e.target.style.display='none'" />
              <div style="font-size: 0.65rem; color: #6b7280; margin-top: 2px; text-align: center;">Foto saat ini</div>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
          <button @click="showFormModal = false" class="btn btn-secondary">Batal</button>
          <button @click="saveForm" class="btn btn-primary" :disabled="isSaving">
            {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

export default {
  name: 'ReviewDailyActivity',
  components: { LoadingSpinner },

  data() {
    const today = new Date().toISOString().slice(0, 10);
    return {
      isLoading: false,
      isSaving: false,
      hasFetched: false,
      items: [],
      lokasiList: [],
      securityList: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 25,

      filter: {
        tgl_awal: today.slice(0, 8) + '01',
        tgl_akhir: today,
        location_id: '',
        kode_karyawan: '',
      },

      showPhotoModal: false,
      photoItem: null,
      lightboxUrl: null,

      showFormModal: false,
      isEditing: false,
      form: this.defaultForm(),
      previews: { 1: null, 2: null, 3: null },
      newBase64: { 1: null, 2: null, 3: null },
    };
  },

  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(i =>
        (i.NamaKaryawan || '').toLowerCase().includes(q) ||
        (i.KodeKaryawan || '').toLowerCase().includes(q) ||
        (i.DeskripsiAktifitas || '').toLowerCase().includes(q) ||
        (i.NamaLokasi || '').toLowerCase().includes(q)
      );
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredItems.length / this.itemsPerPage));
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredItems.slice(start, start + this.itemsPerPage);
    },
    filteredSecurityList() {
      if (!this.filter.location_id) return this.securityList;
      return this.securityList.filter(s => String(s.LocationID) === String(this.filter.location_id));
    },
    formSecurityList() {
      if (!this.form.LocationID) return this.securityList;
      return this.securityList.filter(s => String(s.LocationID) === String(this.form.LocationID));
    },
  },

  watch: {
    searchQuery() { this.currentPage = 1; },
    itemsPerPage() { this.currentPage = 1; },
  },

  mounted() {
    this.fetchLokasi();
    this.fetchSecurity();
  },

  methods: {
    defaultForm() {
      return {
        id: null,
        Tanggal: '',
        DeskripsiAktifitas: '',
        LocationID: '',
        KodeKaryawan: '',
        NamaKaryawan: '',
        Gambar1: '',
        Gambar2: '',
        Gambar3: '',
      };
    },

    async fetchLokasi() {
      try {
        const res = await axios.get('/lokasi-patroli');
        this.lokasiList = res.data.data || [];
      } catch (_) {}
    },

    async fetchSecurity() {
      try {
        const res = await axios.get('/master-security');
        this.securityList = res.data.data || [];
      } catch (_) {}
    },

    async fetchData() {
      if (!this.filter.tgl_awal || !this.filter.tgl_akhir) {
        Swal.fire('Perhatian', 'Tgl Awal dan Tgl Akhir harus diisi.', 'warning');
        return;
      }
      this.isLoading = true;
      this.currentPage = 1;
      try {
        const params = { tgl_awal: this.filter.tgl_awal, tgl_akhir: this.filter.tgl_akhir };
        if (this.filter.location_id)   params.location_id   = this.filter.location_id;
        if (this.filter.kode_karyawan) params.kode_karyawan = this.filter.kode_karyawan;
        const res = await axios.get('/review-daily-activity', { params });
        this.items = res.data.data || [];
        this.hasFetched = true;
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data.', 'error');
      } finally {
        this.isLoading = false;
      }
    },

    onFilterLokasiChange() {
      this.filter.kode_karyawan = '';
    },

    onFormLokasiChange() {
      this.form.KodeKaryawan = '';
      this.form.NamaKaryawan = '';
    },

    onSecurityChange() {
      const s = this.securityList.find(x => x.NIK === this.form.KodeKaryawan);
      this.form.NamaKaryawan = s ? s.NamaSecurity : '';
    },

    openCreate() {
      this.form = this.defaultForm();
      const now = new Date();
      this.form.Tanggal = now.toISOString().slice(0, 16);
      this.previews = { 1: null, 2: null, 3: null };
      this.newBase64 = { 1: null, 2: null, 3: null };
      this.isEditing = false;
      this.showFormModal = true;
    },

    openEdit(item) {
      this.form = {
        id: item.id,
        Tanggal: this.dbToDatetimeLocal(item.Tanggal),
        DeskripsiAktifitas: item.DeskripsiAktifitas || '',
        LocationID: item.LocationID || '',
        KodeKaryawan: item.KodeKaryawan || '',
        NamaKaryawan: item.NamaKaryawan || '',
        Gambar1Url: item.Gambar1Url || '',
        Gambar2Url: item.Gambar2Url || '',
        Gambar3Url: item.Gambar3Url || '',
      };
      this.previews = { 1: null, 2: null, 3: null };
      this.newBase64 = { 1: null, 2: null, 3: null };
      this.isEditing = true;
      this.showFormModal = true;
    },

    async saveForm() {
      if (!this.form.Tanggal || !this.form.DeskripsiAktifitas || !this.form.LocationID || !this.form.KodeKaryawan) {
        Swal.fire('Perhatian', 'Field bertanda * wajib diisi.', 'warning');
        return;
      }

      this.isSaving = true;
      try {
        const payload = {
          Tanggal:            this.datetimeLocalToDb(this.form.Tanggal),
          DeskripsiAktifitas: this.form.DeskripsiAktifitas,
          LocationID:         this.form.LocationID,
          KodeKaryawan:       this.form.KodeKaryawan,
          NamaKaryawan:       this.form.NamaKaryawan || null,
        };
        if (this.newBase64[1]) payload.Gambar1Base64 = this.newBase64[1];
        if (this.newBase64[2]) payload.Gambar2Base64 = this.newBase64[2];
        if (this.newBase64[3]) payload.Gambar3Base64 = this.newBase64[3];

        if (this.isEditing) {
          await axios.put(`/review-daily-activity/${this.form.id}`, payload);
        } else {
          await axios.post('/review-daily-activity', payload);
        }

        this.showFormModal = false;
        if (this.hasFetched) await this.fetchData();
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data berhasil disimpan.', timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Terjadi kesalahan.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async deleteItem(item) {
      const result = await Swal.fire({
        title: 'Hapus data aktivitas ini?',
        html: `<b>${item.NamaKaryawan || item.KodeKaryawan}</b><br>${this.formatDatetime(item.Tanggal)}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
      });
      if (!result.isConfirmed) return;

      try {
        await axios.delete(`/review-daily-activity/${item.id}`);
        this.items = this.items.filter(i => i.id !== item.id);
        Swal.fire({ icon: 'success', title: 'Dihapus!', text: 'Data berhasil dihapus.', timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal menghapus.', 'error');
      }
    },

    openPhoto(item) {
      this.photoItem = item;
      this.showPhotoModal = true;
    },

    openLightbox(url) {
      this.lightboxUrl = url;
    },

    onImageChange(n, event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        this.previews[n] = e.target.result;
        this.newBase64[n] = e.target.result;
      };
      reader.readAsDataURL(file);
    },

    exportExcel() {
      const rows = this.filteredItems.map((item, idx) => ({
        'No': idx + 1,
        'Tanggal & Waktu': this.formatDatetime(item.Tanggal),
        'NIK': item.KodeKaryawan,
        'Nama Security': item.NamaKaryawan || '-',
        'Deskripsi Aktivitas': item.DeskripsiAktifitas,
        'Lokasi': item.NamaLokasi || '-',
        'Foto 1': item.Gambar1 ? 'Ada' : '-',
        'Foto 2': item.Gambar2 ? 'Ada' : '-',
        'Foto 3': item.Gambar3 ? 'Ada' : '-',
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [
        { wch: 5 }, { wch: 18 }, { wch: 14 }, { wch: 26 },
        { wch: 40 }, { wch: 22 }, { wch: 8 }, { wch: 8 }, { wch: 8 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Daily Activity');
      XLSX.writeFile(wb, `DailyActivity_${this.filter.tgl_awal}_${this.filter.tgl_akhir}.xlsx`);
    },

    // ---- Helpers ----
    formatDatetime(val) {
      if (!val) return '-';
      const str = String(val).trim();
      if (str.length < 16) return str;
      const parts = str.split(' ');
      if (parts.length === 2) {
        const [y, m, d] = parts[0].split('-');
        return `${d}/${m}/${y} ${parts[1].slice(0, 5)}`;
      }
      return str.slice(0, 16);
    },

    dbToDatetimeLocal(val) {
      if (!val) return '';
      const str = String(val).trim();
      if (str.length < 16) return '';
      return str.slice(0, 10) + 'T' + str.slice(11, 16);
    },

    datetimeLocalToDb(val) {
      if (!val) return null;
      return val.replace('T', ' ') + ':00';
    },

    imageUrl(filename) {
      if (!filename) return '';
      // Fallback jika tidak ada URL lengkap dari backend
      const base = 'http://192.168.1.8:8000';
      return `${base}/activity/${filename}`;
    },
  },
};
</script>