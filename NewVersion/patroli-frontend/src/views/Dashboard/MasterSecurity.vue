<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Master Security</h1>
      <button @click="openCreateModal" class="btn btn-primary">+ Tambah Security</button>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
      <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 180px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Lokasi</label>
          <select v-model="filterLocationID" class="input">
            <option value="">Semua Lokasi</option>
            <option v-for="loc in lokasiList" :key="loc.id" :value="loc.id">{{ loc.NamaArea }}</option>
          </select>
        </div>
        <div style="min-width: 150px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Status</label>
          <select v-model="filterStatus" class="input">
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
          </select>
        </div>
        <div>
          <button @click="fetchItems" class="btn btn-secondary">Cari</button>
        </div>
        <div style="flex: 1; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Cari Nama / NIK</label>
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
                <th>Foto</th>
                <th>NIK</th>
                <th>Nama Security</th>
                <th>Tgl Bergabung</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="paginatedItems.length === 0">
                <td colspan="7" style="text-align: center; color: #888; padding: 2rem;">
                  Tidak ada data security.
                </td>
              </tr>
              <tr v-for="item in paginatedItems" :key="item.NIK">
                <td style="text-align: center;">
                  <span
                    v-if="item.has_image"
                    title="Foto tersedia"
                    style="color: #38a169; font-size: 1.25rem; font-weight: 700;"
                  >&#10003;</span>
                  <span
                    v-else
                    title="Belum ada foto"
                    style="color: #e53e3e; font-size: 1.25rem; font-weight: 700;"
                  >&#10007;</span>
                </td>
                <td style="font-weight: 500;">{{ item.NIK }}</td>
                <td>{{ item.NamaSecurity }}</td>
                <td>{{ formatDate(item.JoinDate) }}</td>
                <td>{{ item.lokasi?.NamaArea || '-' }}</td>
                <td>
                  <span
                    class="badge"
                    :style="item.Status == 1 ? 'background:#c6f6d5;color:#276749;' : 'background:#fed7d7;color:#9b2c2c;'"
                  >
                    {{ item.Status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button @click="editItem(item.NIK)" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Edit</button>
                    <button
                      @click="$router.push({ name: 'JadwalSecurity', params: { nik: item.NIK }, query: { nama: item.NamaSecurity } })"
                      class="btn btn-primary"
                      style="padding: 0.25rem 0.75rem; font-size: 0.85rem;"
                    >Atur Jadwal</button>
                    <button @click="deleteItem(item.NIK, item.NamaSecurity)" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Hapus</button>
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
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: flex-start; justify-content: center; z-index: 1000; padding: 1rem; overflow-y: auto;"
    >
      <div class="card" style="width: 100%; max-width: 640px; margin: auto;">

        <!-- Header modal -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit Security' : 'Tambah Security' }}</h2>
          <button @click="closeModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- NIK -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Nomer Induk Karyawan (NIK) <span style="color:#e53e3e;">*</span>
          </label>
          <input
            v-model="form.NIK"
            class="input"
            placeholder="Nomer Induk Karyawan"
            :readonly="isEditing"
            :style="isEditing ? 'background:#f7fafc; cursor:not-allowed; color:#718096;' : ''"
          />
          <p v-if="isEditing" style="font-size: 0.75rem; color: #718096; margin: 0.25rem 0 0;">NIK tidak dapat diubah setelah disimpan.</p>
        </div>

        <!-- Nama -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Nama Karyawan <span style="color:#e53e3e;">*</span>
          </label>
          <input v-model="form.NamaSecurity" class="input" placeholder="Nama lengkap karyawan" />
        </div>

        <!-- Tgl Bergabung -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Tanggal Bergabung <span style="color:#e53e3e;">*</span>
          </label>
          <input type="date" v-model="form.JoinDate" class="input" />
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

        <!-- Status -->
        <div style="margin-bottom: 1.25rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Status <span style="color:#e53e3e;">*</span>
          </label>
          <select v-model="form.Status" class="input">
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
          </select>
        </div>

        <!-- ===== FOTO ===== -->
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Foto</label>

          <!-- Preview foto / placeholder -->
          <div style="margin-bottom: 0.75rem;">
            <img
              v-if="form.Image"
              :src="form.Image"
              style="max-width: 320px; max-height: 240px; border-radius: 6px; border: 1px solid #e2e8f0; display: block;"
              alt="Foto Security"
            />
            <div
              v-else
              style="width: 320px; height: 180px; background: #f7fafc; border: 2px dashed #cbd5e0; border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #a0aec0;"
            >
              <span style="font-size: 2rem; margin-bottom: 0.25rem;">&#128247;</span>
              <span style="font-size: 0.85rem;">Belum ada foto</span>
            </div>
          </div>

          <!-- Video stream kamera -->
          <div v-if="cameraActive" style="margin-bottom: 0.75rem;">
            <video
              ref="cameraVideo"
              autoplay
              playsinline
              muted
              style="width: 320px; height: 240px; border-radius: 6px; border: 1px solid #3182ce; background: #000; display: block;"
            ></video>
          </div>

          <!-- Tombol aksi foto -->
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button
              v-if="!cameraActive"
              @click="openCamera"
              type="button"
              class="btn btn-secondary"
            >
              &#128247; Buka Kamera
            </button>
            <button
              v-if="cameraActive"
              @click="capturePhoto"
              type="button"
              class="btn btn-primary"
            >
              Ambil Foto
            </button>
            <button
              v-if="cameraActive"
              @click="closeCamera"
              type="button"
              class="btn btn-secondary"
            >
              Tutup Kamera
            </button>
            <label class="btn btn-secondary" style="cursor: pointer; margin: 0; display: inline-flex; align-items: center;">
              &#128193; Upload dari File
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileUpload"
                style="display: none;"
              />
            </label>
            <button
              v-if="form.Image"
              @click="clearPhoto"
              type="button"
              class="btn btn-danger"
            >
              Hapus Foto
            </button>
          </div>

          <p style="font-size: 0.75rem; color: #718096; margin: 0.35rem 0 0;">
            Foto disimpan sebagai base64. Gunakan kamera atau upload file gambar.
          </p>
        </div>

        <!-- Footer actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
          <button @click="closeModal" class="btn btn-secondary">Batal</button>
          <button @click="saveItem" class="btn btn-primary" :disabled="isSaving">
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

export default {
  name: 'MasterSecurity',
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
      filterStatus: '',
      currentPage: 1,
      itemsPerPage: 10,
      form: this.defaultForm(),

      // Camera
      cameraActive: false,
      cameraStream: null,
    };
  },

  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(
        (i) =>
          i.NIK?.toLowerCase().includes(q) ||
          i.NamaSecurity?.toLowerCase().includes(q),
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

  beforeUnmount() {
    this.closeCamera();
  },

  methods: {
    defaultForm() {
      return {
        NIK: '',
        NamaSecurity: '',
        JoinDate: '',
        LocationID: '',
        Status: '1',
        Image: '',
      };
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
        if (this.filterStatus !== '') params.Status = this.filterStatus;

        const res = await axios.get('/master-security', { params });
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

    async editItem(nik) {
      Swal.fire({
        title: 'Memuat data...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const res = await axios.get(`/master-security/${encodeURIComponent(nik)}`);
        const data = res.data.data;

        this.form = {
          NIK: data.NIK,
          NamaSecurity: data.NamaSecurity,
          JoinDate: data.JoinDate ? data.JoinDate.split('T')[0].split(' ')[0] : '',
          LocationID: data.LocationID,
          Status: String(data.Status),
          Image: data.Image || '',
        };
        this.isEditing = true;
        this.showModal = true;
        Swal.close();
      } catch (e) {
        Swal.fire('Error', 'Gagal memuat data security.', 'error');
      }
    },

    closeModal() {
      this.showModal = false;
      this.closeCamera();
    },

    async saveItem() {
      if (!this.form.NIK.trim()) {
        Swal.fire('Perhatian', 'NIK tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.NamaSecurity.trim()) {
        Swal.fire('Perhatian', 'Nama Karyawan tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.JoinDate) {
        Swal.fire('Perhatian', 'Tanggal Bergabung tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.LocationID) {
        Swal.fire('Perhatian', 'Lokasi Patroli harus dipilih.', 'warning');
        return;
      }

      this.isSaving = true;
      try {
        if (this.isEditing) {
          await axios.put(`/master-security/${encodeURIComponent(this.form.NIK)}`, this.form);
        } else {
          await axios.post('/master-security', this.form);
        }
        this.closeModal();
        await this.fetchItems();
        Swal.fire('Berhasil!', 'Data security berhasil disimpan.', 'success');
      } catch (e) {
        const msg = e.response?.data?.message || 'Terjadi kesalahan saat menyimpan.';
        Swal.fire('Error', msg, 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async deleteItem(nik, nama) {
      const result = await Swal.fire({
        title: 'Yakin hapus security ini?',
        html: `NIK: <strong>${nik}</strong><br>Nama: <strong>${nama}</strong>`,
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
        await axios.delete(`/master-security/${encodeURIComponent(nik)}`);
        await this.fetchItems();
        Swal.fire('Dihapus!', 'Data security berhasil dihapus.', 'success');
      } catch (e) {
        Swal.fire('Gagal', e.response?.data?.message || 'Gagal menghapus data.', 'error');
      }
    },

    formatDate(val) {
      if (!val) return '-';
      const d = new Date(val);
      if (isNaN(d)) return val;
      return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    // ===== CAMERA =====
    async openCamera() {
      if (!navigator.mediaDevices?.getUserMedia) {
        Swal.fire('Tidak Didukung', 'Browser ini tidak mendukung akses kamera.', 'error');
        return;
      }
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        this.cameraStream = stream;
        this.cameraActive = true;
        this.$nextTick(() => {
          if (this.$refs.cameraVideo) {
            this.$refs.cameraVideo.srcObject = stream;
          }
        });
      } catch (e) {
        const msg =
          e.name === 'NotAllowedError'
            ? 'Izin kamera ditolak. Silakan izinkan akses kamera di browser.'
            : 'Tidak dapat mengakses kamera: ' + e.message;
        Swal.fire('Akses Kamera Gagal', msg, 'error');
      }
    },

    capturePhoto() {
      const video = this.$refs.cameraVideo;
      if (!video) return;

      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth || 320;
      canvas.height = video.videoHeight || 240;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      this.form.Image = canvas.toDataURL('image/jpeg', 0.85);
      this.closeCamera();
    },

    closeCamera() {
      if (this.cameraStream) {
        this.cameraStream.getTracks().forEach((t) => t.stop());
        this.cameraStream = null;
      }
      this.cameraActive = false;
    },

    handleFileUpload(e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        this.form.Image = ev.target.result;
      };
      reader.readAsDataURL(file);
      e.target.value = '';
    },

    clearPhoto() {
      this.form.Image = '';
      this.closeCamera();
    },
  },
};
</script>