<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Review Buku Tamu</h1>
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
          <select v-model="filter.location_id" class="input" style="width: 200px;">
            <option value="">Semua Lokasi</option>
            <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
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
      <input v-model="searchQuery" placeholder="Cari nama tamu, yang dicari, tujuan..." class="input" style="width: 320px;" />
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
                <th style="white-space: nowrap;">Tanggal</th>
                <th style="white-space: nowrap;">Nama Tamu</th>
                <th style="white-space: nowrap;">Yang Dicari</th>
                <th>Tujuan</th>
                <th style="white-space: nowrap;">Jam Masuk</th>
                <th style="white-space: nowrap;">Jam Keluar</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th style="white-space: nowrap;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in paginatedItems" :key="item.id">
                <td>{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="white-space: nowrap;">{{ formatDate(item.Tanggal) }}</td>
                <td>{{ item.NamaTamu }}</td>
                <td>{{ item.NamaYangDicari }}</td>
                <td>{{ item.Tujuan }}</td>
                <td style="white-space: nowrap;">{{ formatDatetime(item.TglMasuk) || '-' }}</td>
                <td style="white-space: nowrap;">{{ formatDatetime(item.TglKeluar) || '-' }}</td>
                <td>{{ item.NamaLokasi || '-' }}</td>
                <td style="max-width: 160px; word-break: break-word;">{{ item.Keterangan || '-' }}</td>
                <td>
                  <div style="display: flex; gap: 0.4rem; flex-wrap: nowrap;">
                    <button v-if="item.ImageIn || item.ImageOut" @click="openPhoto(item)" class="btn btn-secondary" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">&#128247;</button>
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
      <div class="card" style="width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
          <div>
            <div style="font-weight: 700; font-size: 1rem; color: #111827;">{{ photoItem.NamaTamu }}</div>
            <div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">{{ formatDate(photoItem.Tanggal) }}</div>
          </div>
          <button @click="showPhotoModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
          <div style="background: #f9fafb; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Yang Dicari</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: #374151; margin-top: 2px;">{{ photoItem.NamaYangDicari }}</div>
          </div>
          <div style="background: #f9fafb; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Tujuan</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: #374151; margin-top: 2px;">{{ photoItem.Tujuan }}</div>
          </div>
          <div style="background: #f0fdf4; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Jam Masuk</div>
            <div style="font-size: 0.875rem; font-weight: 700; color: #15803d; margin-top: 2px;">{{ formatDatetime(photoItem.TglMasuk) || '-' }}</div>
          </div>
          <div style="background: #f9fafb; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Jam Keluar</div>
            <div style="font-size: 0.875rem; font-weight: 700; color: #374151; margin-top: 2px;">{{ formatDatetime(photoItem.TglKeluar) || '-' }}</div>
          </div>
        </div>

        <div v-if="photoItem.NamaLokasi || photoItem.Keterangan" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
          <div v-if="photoItem.NamaLokasi" style="background: #f9fafb; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Lokasi</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: #374151; margin-top: 2px;">{{ photoItem.NamaLokasi }}</div>
          </div>
          <div v-if="photoItem.Keterangan" style="background: #fefce8; border-radius: 6px; padding: 0.6rem 0.75rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Keterangan</div>
            <div style="font-size: 0.875rem; color: #374151; margin-top: 2px;">{{ photoItem.Keterangan }}</div>
          </div>
        </div>

        <div v-if="photoItem.ImageInUrl" style="margin-bottom: 1rem;">
          <div style="font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem;">&#128247; Foto Masuk</div>
          <img :src="photoItem.ImageInUrl" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; cursor: pointer;" @click="openLightbox(photoItem.ImageInUrl)" @error="(e) => e.target.style.display='none'" />
        </div>
        <div v-if="photoItem.ImageOutUrl" style="margin-bottom: 0.5rem;">
          <div style="font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem;">&#128247; Foto Keluar</div>
          <img :src="photoItem.ImageOutUrl" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; cursor: pointer;" @click="openLightbox(photoItem.ImageOutUrl)" @error="(e) => e.target.style.display='none'" />
        </div>
      </div>
    </div>

    <!-- Modal Form CRUD -->
    <div v-if="showFormModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="showFormModal = false">
      <div class="card" style="width: 100%; max-width: 640px; max-height: 92vh; overflow-y: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit Data Tamu' : 'Tambah Data Tamu' }}</h2>
          <button @click="showFormModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
        </div>

        <!-- Row 1: Tanggal + Lokasi -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Tanggal <span style="color:#ef4444;">*</span></label>
            <input v-model="form.Tanggal" type="date" class="input" />
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Lokasi</label>
            <select v-model="form.LocationID" class="input">
              <option value="">- Pilih Lokasi -</option>
              <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
            </select>
          </div>
        </div>

        <!-- Nama Tamu -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Nama Tamu <span style="color:#ef4444;">*</span></label>
          <input v-model="form.NamaTamu" type="text" class="input" placeholder="Nama lengkap tamu..." />
        </div>

        <!-- Yang Dicari -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Yang Dicari <span style="color:#ef4444;">*</span></label>
          <input v-model="form.NamaYangDicari" type="text" class="input" placeholder="Nama orang yang ingin ditemui..." />
        </div>

        <!-- Tujuan -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Tujuan <span style="color:#ef4444;">*</span></label>
          <input v-model="form.Tujuan" type="text" class="input" placeholder="Tujuan kunjungan..." />
        </div>

        <!-- Row: Jam Masuk + Jam Keluar -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Jam Masuk <span style="color:#ef4444;">*</span></label>
            <input v-model="form.TglMasuk" type="datetime-local" class="input" />
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Jam Keluar</label>
            <input v-model="form.TglKeluar" type="datetime-local" class="input" />
          </div>
        </div>

        <!-- Keterangan -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Keterangan</label>
          <input v-model="form.Keterangan" type="text" class="input" placeholder="Catatan tambahan (opsional)..." />
        </div>

        <!-- Row: Foto Masuk + Foto Keluar -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Foto Masuk</label>
            <input type="file" accept="image/*" class="input" style="padding: 0.3rem;" @change="onImageChange('In', $event)" />
            <div v-if="previewIn" style="margin-top: 0.5rem;">
              <img :src="previewIn" style="width: 100%; max-height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" />
            </div>
            <div v-else-if="isEditing && form.ImageInUrl" style="margin-top: 0.5rem;">
              <img :src="form.ImageInUrl" style="width: 100%; max-height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" @error="(e) => e.target.style.display='none'" />
              <div style="font-size: 0.7rem; color: #6b7280; margin-top: 2px;">Foto saat ini</div>
            </div>
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #374151; margin-bottom: 0.25rem;">Foto Keluar</label>
            <input type="file" accept="image/*" class="input" style="padding: 0.3rem;" @change="onImageChange('Out', $event)" />
            <div v-if="previewOut" style="margin-top: 0.5rem;">
              <img :src="previewOut" style="width: 100%; max-height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" />
            </div>
            <div v-else-if="isEditing && form.ImageOutUrl" style="margin-top: 0.5rem;">
              <img :src="form.ImageOutUrl" style="width: 100%; max-height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" @error="(e) => e.target.style.display='none'" />
              <div style="font-size: 0.7rem; color: #6b7280; margin-top: 2px;">Foto saat ini</div>
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
  name: 'ReviewBukuTamu',
  components: { LoadingSpinner },

  data() {
    const today = new Date().toISOString().slice(0, 10);
    return {
      isLoading: false,
      isSaving: false,
      hasFetched: false,
      items: [],
      lokasiList: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 25,

      filter: {
        tgl_awal: today.slice(0, 8) + '01',
        tgl_akhir: today,
        location_id: '',
      },

      // Photo modal
      showPhotoModal: false,
      photoItem: null,

      // Form modal
      showFormModal: false,
      isEditing: false,
      form: this.defaultForm(),
      previewIn: null,
      previewOut: null,
      newImageInBase64: null,
      newImageOutBase64: null,
    };
  },

  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(i =>
        (i.NamaTamu || '').toLowerCase().includes(q) ||
        (i.NamaYangDicari || '').toLowerCase().includes(q) ||
        (i.Tujuan || '').toLowerCase().includes(q) ||
        (i.Keterangan || '').toLowerCase().includes(q)
      );
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredItems.length / this.itemsPerPage));
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredItems.slice(start, start + this.itemsPerPage);
    },
  },

  watch: {
    searchQuery() { this.currentPage = 1; },
    itemsPerPage() { this.currentPage = 1; },
  },

  mounted() {
    this.fetchLokasi();
  },

  methods: {
    defaultForm() {
      return {
        id: null,
        Tanggal: '',
        NamaTamu: '',
        NamaYangDicari: '',
        Tujuan: '',
        TglMasuk: '',
        TglKeluar: '',
        LocationID: '',
        Keterangan: '',
        ImageIn: '',
        ImageOut: '',
      };
    },

    async fetchLokasi() {
      try {
        const res = await axios.get('/lokasi-patroli');
        this.lokasiList = res.data.data || [];
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
        if (this.filter.location_id) params.location_id = this.filter.location_id;
        const res = await axios.get('/review-bukutamu', { params });
        this.items = res.data.data || [];
        this.hasFetched = true;
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data.', 'error');
      } finally {
        this.isLoading = false;
      }
    },

    openCreate() {
      this.form = this.defaultForm();
      const today = new Date().toISOString().slice(0, 10);
      this.form.Tanggal = today;
      this.form.TglMasuk = today + 'T08:00';
      this.previewIn = null;
      this.previewOut = null;
      this.newImageInBase64 = null;
      this.newImageOutBase64 = null;
      this.isEditing = false;
      this.showFormModal = true;
    },

    openEdit(item) {
      this.form = {
        id: item.id,
        Tanggal: String(item.Tanggal).slice(0, 10),
        NamaTamu: item.NamaTamu || '',
        NamaYangDicari: item.NamaYangDicari || '',
        Tujuan: item.Tujuan || '',
        TglMasuk: this.dbToDatetimeLocal(item.TglMasuk),
        TglKeluar: this.dbToDatetimeLocal(item.TglKeluar),
        LocationID: item.LocationID || '',
        Keterangan: item.Keterangan || '',
        ImageInUrl: item.ImageInUrl || '',
        ImageOutUrl: item.ImageOutUrl || '',
      };
      this.previewIn = null;
      this.previewOut = null;
      this.newImageInBase64 = null;
      this.newImageOutBase64 = null;
      this.isEditing = true;
      this.showFormModal = true;
    },

    async saveForm() {
      if (!this.form.NamaTamu || !this.form.NamaYangDicari || !this.form.Tujuan || !this.form.Tanggal || !this.form.TglMasuk) {
        Swal.fire('Perhatian', 'Field bertanda * wajib diisi.', 'warning');
        return;
      }

      this.isSaving = true;
      try {
        const payload = {
          NamaTamu:       this.form.NamaTamu,
          NamaYangDicari: this.form.NamaYangDicari,
          Tujuan:         this.form.Tujuan,
          Tanggal:        this.form.Tanggal,
          TglMasuk:       this.datetimeLocalToDb(this.form.TglMasuk),
          TglKeluar:      this.form.TglKeluar ? this.datetimeLocalToDb(this.form.TglKeluar) : null,
          LocationID:     this.form.LocationID || null,
          Keterangan:     this.form.Keterangan || null,
        };

        if (this.newImageInBase64)  payload.ImageInBase64  = this.newImageInBase64;
        if (this.newImageOutBase64) payload.ImageOutBase64 = this.newImageOutBase64;

        if (this.isEditing) {
          await axios.put(`/review-bukutamu/${this.form.id}`, payload);
        } else {
          await axios.post('/review-bukutamu', payload);
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
        title: 'Hapus data tamu ini?',
        html: `<b>${item.NamaTamu}</b> — ${this.formatDate(item.Tanggal)}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
      });
      if (!result.isConfirmed) return;

      try {
        await axios.delete(`/review-bukutamu/${item.id}`);
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

    onImageChange(side, event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        if (side === 'In') {
          this.previewIn = e.target.result;
          this.newImageInBase64 = e.target.result;
        } else {
          this.previewOut = e.target.result;
          this.newImageOutBase64 = e.target.result;
        }
      };
      reader.readAsDataURL(file);
    },

    exportExcel() {
      const rows = this.filteredItems.map((item, idx) => ({
        'No': idx + 1,
        'Tanggal': this.formatDate(item.Tanggal),
        'Nama Tamu': item.NamaTamu,
        'Yang Dicari': item.NamaYangDicari,
        'Tujuan': item.Tujuan,
        'Jam Masuk': this.formatDatetime(item.TglMasuk) || '-',
        'Jam Keluar': this.formatDatetime(item.TglKeluar) || '-',
        'Lokasi': item.NamaLokasi || '-',
        'Keterangan': item.Keterangan || '-',
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [
        { wch: 5 }, { wch: 12 }, { wch: 24 }, { wch: 24 },
        { wch: 28 }, { wch: 18 }, { wch: 18 }, { wch: 20 }, { wch: 28 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Buku Tamu');
      XLSX.writeFile(wb, `BukuTamu_${this.filter.tgl_awal}_${this.filter.tgl_akhir}.xlsx`);
    },

    // ---- Helpers ----
    formatDate(dateStr) {
      if (!dateStr) return '-';
      const [y, m, d] = String(dateStr).slice(0, 10).split('-');
      return `${d}/${m}/${y}`;
    },

    formatDatetime(val) {
      if (!val) return null;
      const str = String(val).trim();
      if (str.length <= 8 || str === '00:00:00') return null;
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
      if (str.length < 16 || str === '00:00:00') return '';
      // YYYY-MM-DD HH:MM:SS → YYYY-MM-DDTHH:MM
      return str.slice(0, 10) + 'T' + str.slice(11, 16);
    },

    datetimeLocalToDb(val) {
      if (!val) return null;
      // YYYY-MM-DDTHH:MM → YYYY-MM-DD HH:MM:00
      return val.replace('T', ' ') + ':00';
    },

    imageUrl(filename) {
      if (!filename) return '';
      const base = 'http://192.168.1.8:8000';
      return `${base}/guestlog/${filename}`;
    },
    openLightbox(url) {
      this.$swal.fire({
        imageUrl: url,
        imageAlt: 'Foto Tamu',
        showConfirmButton: false,
        showCloseButton: true,
        width: 'auto'
      });
    }
  },
};
</script>