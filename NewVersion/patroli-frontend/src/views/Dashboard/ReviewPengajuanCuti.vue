<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Review Pengajuan Cuti</h1>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div style="flex: 1; min-width: 140px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tgl Pencatatan Awal</label>
          <input type="date" v-model="filter.tgl_awal" class="input" />
        </div>
        <div style="flex: 1; min-width: 140px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tgl Pencatatan Akhir</label>
          <input type="date" v-model="filter.tgl_akhir" class="input" />
        </div>
        <div style="flex: 1; min-width: 140px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Kategori</label>
          <select v-model="filter.kategori" class="input">
            <option value="">Semua Kategori</option>
            <option v-for="k in kategoriList" :key="k" :value="k">{{ k }}</option>
          </select>
        </div>
        <div style="flex: 1; min-width: 140px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Status</label>
          <select v-model="filter.approval" class="input">
            <option value="">Semua Status</option>
            <option value="0">Pending</option>
            <option value="1">Disetujui</option>
            <option value="2">Ditolak</option>
          </select>
        </div>
        <div style="flex: 1; min-width: 160px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">NIK Karyawan</label>
          <input v-model="filter.kode_karyawan" class="input" placeholder="Cari NIK..." />
        </div>
        <div>
          <button @click="fetchData" class="btn btn-primary" :disabled="isLoading">
            {{ isLoading ? 'Memuat...' : 'Tampilkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card table-container" style="position: relative; min-height: 200px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
          <span style="font-weight: 600; color: #374151;">
            Data Pengajuan Cuti
            <span v-if="items.length" style="font-weight: 400; color: #6b7280; margin-left: 0.5rem;">({{ filteredItems.length }} data)</span>
          </span>
          <input v-model="searchQuery" placeholder="Cari NIK / Nama / keterangan..." class="input" style="width: 240px;" />
        </div>

        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th style="width: 40px;">No</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Tgl Pencatatan</th>
                <th>Periode Cuti</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th style="text-align: center;">Foto</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!hasSearched && items.length === 0">
                <td colspan="10" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data.
                </td>
              </tr>
              <tr v-else-if="filteredItems.length === 0">
                <td colspan="10" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada data.</td>
              </tr>
              <tr v-for="(item, idx) in paginatedItems" :key="item.id">
                <td>{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="font-weight: 500;">{{ item.KodeKaryawan }}</td>
                <td>{{ item.NamaSecurity }}</td>
                <td style="white-space: nowrap;">{{ item.TglPencatatan }}</td>
                <td style="white-space: nowrap;">{{ item.TglCutiAwal }} s/d {{ item.TglCutiAkhir }}</td>
                <td>
                  <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.75rem;">
                    {{ item.KategoriCuti }}
                  </span>
                </td>
                <td style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="item.KeteranganCuti">
                  {{ item.KeteranganCuti }}
                </td>
                <td style="text-align: center;">
                  <span v-if="item.fotos && item.fotos.length">
                    <button @click="openFotoModal(item.fotos)" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">
                      &#128247; {{ item.fotos.length }}
                    </button>
                  </span>
                  <span v-else style="color: #9ca3af; font-size: 0.75rem;">—</span>
                </td>
                <td style="text-align: center;">
                  <span :class="badgeClass(item.Approval)" class="badge">{{ approvalLabel(item.Approval) }}</span>
                </td>
                <td style="text-align: center; white-space: nowrap;">
                  <button
                    v-if="item.Approval === 0"
                    @click="openApproveModal(item, 1)"
                    class="btn btn-primary"
                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem; margin-right: 4px;"
                  >Setujui</button>
                  <button
                    v-if="item.Approval === 0"
                    @click="openApproveModal(item, 2)"
                    class="btn btn-danger"
                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem;"
                  >Tolak</button>
                  <span v-if="item.Approval !== 0" style="font-size: 0.75rem; color: #6b7280;">
                    {{ item.ApprovedBy }} · {{ item.ApprovedOn ?? '' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.875rem;">Show</span>
            <select v-model="itemsPerPage" class="input" style="width: 70px;">
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
            <span style="font-size: 0.875rem;">per halaman</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary">&#8592;</button>
            <span style="font-size: 0.875rem;">{{ currentPage }} / {{ totalPages || 1 }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++" class="btn btn-secondary">&#8594;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Approve/Reject -->
    <div v-if="showApproveModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;">
      <div class="card" style="width: 100%; max-width: 480px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">
            {{ approveAction === 1 ? '✅ Setujui Cuti' : '❌ Tolak Cuti' }}
          </h2>
          <button @click="showApproveModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <div style="margin-bottom: 1rem; padding: 0.75rem; background: #f9fafb; border-radius: 6px; font-size: 0.875rem;">
          <div><strong>NIK:</strong> {{ selectedItem?.KodeKaryawan }} — {{ selectedItem?.NamaSecurity }}</div>
          <div><strong>Tgl Pencatatan:</strong> {{ selectedItem?.TglPencatatan }}</div>
          <div><strong>Periode:</strong> {{ selectedItem?.TglCutiAwal }} s/d {{ selectedItem?.TglCutiAkhir }}</div>
          <div><strong>Kategori:</strong> {{ selectedItem?.KategoriCuti }}</div>
          <div style="margin-top: 0.25rem;"><strong>Keterangan:</strong> {{ selectedItem?.KeteranganCuti }}</div>
        </div>

        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Catatan (opsional)</label>
          <textarea v-model="catatanApproval" class="input" rows="3" placeholder="Tulis catatan jika perlu..."></textarea>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
          <button @click="showApproveModal = false" class="btn btn-secondary">Batal</button>
          <button
            @click="submitApproval"
            :class="approveAction === 1 ? 'btn btn-primary' : 'btn btn-danger'"
            :disabled="isApproving"
          >
            {{ isApproving ? 'Menyimpan...' : (approveAction === 1 ? 'Setujui' : 'Tolak') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Foto -->
    <div v-if="showFotoModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="showFotoModal = false">
      <div style="background: #fff; border-radius: 12px; padding: 1.5rem; max-width: 640px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0;">Foto Pendukung ({{ selectedFotos.length }})</h3>
          <button @click="showFotoModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
          <a
            v-for="foto in selectedFotos"
            :key="foto.id"
            :href="fotoUrl(foto.FileName)"
            target="_blank"
          >
            <img
              :src="fotoUrl(foto.FileName)"
              style="width: 140px; height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;"
              :alt="foto.FileName"
            />
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import Swal from 'sweetalert2';

export default {
  components: { LoadingSpinner },
  data() {
    return {
      items: [],
      isLoading: false,
      hasSearched: false,
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,

      kategoriList: [],

      filter: {
        tgl_awal: '',
        tgl_akhir: '',
        approval: '',
        kode_karyawan: '',
        kategori: '',
      },

      // Approve/Reject modal
      showApproveModal: false,
      selectedItem: null,
      approveAction: 1,
      catatanApproval: '',
      isApproving: false,

      // Foto modal
      showFotoModal: false,
      selectedFotos: [],
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(
        (i) =>
          i.KodeKaryawan?.toLowerCase().includes(q) ||
          i.NamaSecurity?.toLowerCase().includes(q) ||
          i.KeteranganCuti?.toLowerCase().includes(q) ||
          i.KategoriCuti?.toLowerCase().includes(q),
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
    itemsPerPage() {
      this.currentPage = 1;
    },
  },
  mounted() {
    this.fetchKategori();
  },
  methods: {
    async fetchKategori() {
      try {
        const res = await axios.get('/pengajuan-cuti/kategori');
        this.kategoriList = res.data.data || [];
      } catch (_) {}
    },

    async fetchData() {
      this.isLoading = true;
      this.hasSearched = true;
      try {
        const params = {};
        if (this.filter.tgl_awal) params.tgl_awal = this.filter.tgl_awal;
        if (this.filter.tgl_akhir) params.tgl_akhir = this.filter.tgl_akhir;
        if (this.filter.approval !== '') params.approval = this.filter.approval;
        if (this.filter.kode_karyawan) params.kode_karyawan = this.filter.kode_karyawan;
        if (this.filter.kategori) params.kategori = this.filter.kategori;

        const res = await axios.get('/review-pengajuan-cuti', { params });
        this.items = res.data.data || [];
        this.currentPage = 1;
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data', 'error');
      } finally {
        this.isLoading = false;
      }
    },

    approvalLabel(val) {
      return { 0: 'Pending', 1: 'Disetujui', 2: 'Ditolak' }[val] ?? '-';
    },
    badgeClass(val) {
      return { 0: 'badge-warning', 1: 'badge-success', 2: 'badge-danger' }[val] ?? '';
    },

    openApproveModal(item, action) {
      this.selectedItem = item;
      this.approveAction = action;
      this.catatanApproval = '';
      this.showApproveModal = true;
    },

    async submitApproval() {
      this.isApproving = true;
      try {
        await axios.patch(`/review-pengajuan-cuti/${this.selectedItem.id}/approve`, {
          Approval: this.approveAction,
          CatatanApproval: this.catatanApproval || null,
        });
        this.showApproveModal = false;
        await this.fetchData();
        Swal.fire(
          'Berhasil!',
          this.approveAction === 1 ? 'Cuti berhasil disetujui.' : 'Cuti berhasil ditolak.',
          'success',
        );
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Terjadi kesalahan', 'error');
      } finally {
        this.isApproving = false;
      }
    },

    openFotoModal(fotos) {
      this.selectedFotos = fotos;
      this.showFotoModal = true;
    },

    fotoUrl(fileName) {
      const base = axios.defaults.baseURL.replace('/api', '');
      return `${base}/pengajuan-cuti/${fileName}`;
    },
  },
};
</script>