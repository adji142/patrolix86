<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Lokasi Patroli</h1>
      <div style="display: flex; gap: 1rem; align-items: center;">
        <input v-model="searchQuery" placeholder="Cari lokasi..." class="input" style="width: 250px;" />
        <button @click="openCreateModal" class="btn btn-primary">+ Tambah Lokasi</button>
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
                <th>Nama Lokasi</th>
                <th>Alamat</th>
                <th>Keterangan</th>
                <th>Koordinat</th>
                <th>Radius</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="paginatedItems.length === 0">
                <td colspan="6" style="text-align: center; color: #888; padding: 2rem;">
                  Tidak ada data lokasi patroli.
                </td>
              </tr>
              <tr v-for="item in paginatedItems" :key="item.id">
                <td>{{ item.NamaArea }}</td>
                <td>{{ item.AlamatArea }}</td>
                <td>{{ item.Keterangan || '-' }}</td>
                <td style="font-size: 0.8rem; color: #555;">
                  <span v-if="item.Latitude && item.Longitude">
                    {{ parseFloat(item.Latitude).toFixed(6) }}, {{ parseFloat(item.Longitude).toFixed(6) }}
                  </span>
                  <span v-else style="color: #aaa;">Belum diset</span>
                </td>
                <td>
                  <span v-if="item.Radius" class="badge">{{ item.Radius }} m</span>
                  <span v-else style="color: #aaa;">-</span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.5rem;">
                    <button @click="openShiftModal(item)" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Atur Shift</button>
                    <button @click="editItem(item)" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Edit</button>
                    <button @click="deleteItem(item.id)" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Hapus</button>
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

    <!-- Modal Create/Edit -->
    <div
      v-if="showModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: flex-start; justify-content: center; z-index: 1000; padding: 1rem; overflow-y: auto;"
    >
      <div class="card" style="width: 100%; max-width: 700px; margin: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit Lokasi Patroli' : 'Tambah Lokasi Patroli' }}</h2>
          <button @click="closeModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Nama Lokasi -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Nama Lokasi <span style="color:#e53e3e;">*</span></label>
          <input v-model="form.NamaArea" class="input" placeholder="Contoh: Gedung A, Pos Utama..." />
        </div>

        <!-- Alamat -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Alamat <span style="color:#e53e3e;">*</span></label>
          <input v-model="form.AlamatArea" class="input" placeholder="Alamat lengkap lokasi..." />
        </div>

        <!-- Keterangan -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Keterangan</label>
          <input v-model="form.Keterangan" class="input" placeholder="Keterangan tambahan..." />
        </div>

        <!-- Koordinat row -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
          <div style="flex: 1;">
            <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Latitude</label>
            <input
              v-model="form.Latitude"
              class="input"
              placeholder="Klik peta atau isi manual"
              @change="onCoordChange"
            />
          </div>
          <div style="flex: 1;">
            <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Longitude</label>
            <input
              v-model="form.Longitude"
              class="input"
              placeholder="Klik peta atau isi manual"
              @change="onCoordChange"
            />
          </div>
        </div>

        <!-- Radius slider -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Radius Absensi: <strong>{{ form.Radius }} meter</strong>
          </label>
          <input
            type="range"
            :min="50"
            :max="2000"
            :step="10"
            v-model.number="form.Radius"
            @input="onRadiusChange"
            style="width: 100%; cursor: pointer;"
          />
          <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #888;">
            <span>50 m</span>
            <span>2000 m</span>
          </div>
        </div>

        <!-- Map search -->
        <div style="margin-bottom: 0.5rem; position: relative;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Cari di Peta</label>
          <div style="display: flex; gap: 0.5rem;">
            <input
              v-model="mapSearchQuery"
              class="input"
              placeholder="Cari alamat atau nama tempat..."
              @keydown.enter.prevent="doMapSearch"
              style="flex: 1;"
            />
            <button @click="doMapSearch" class="btn btn-secondary" :disabled="isSearching" style="white-space: nowrap;">
              {{ isSearching ? 'Mencari...' : 'Cari' }}
            </button>
          </div>
          <!-- Dropdown hasil search -->
          <ul
            v-if="searchResults.length"
            style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #ddd; border-radius: 0 0 6px 6px; list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
          >
            <li
              v-for="(result, idx) in searchResults"
              :key="idx"
              @click="selectSearchResult(result)"
              style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem;"
              @mouseenter="$event.target.style.background='#f5f8ff'"
              @mouseleave="$event.target.style.background=''"
            >
              {{ result.display_name }}
            </li>
          </ul>
          <p v-if="searchNoResult" style="font-size: 0.8rem; color: #e53e3e; margin: 0.25rem 0 0;">Lokasi tidak ditemukan.</p>
        </div>

        <!-- Map container -->
        <div style="margin-bottom: 1rem;">
          <div id="lokasi-map" style="height: 350px; border: 1px solid #ddd; border-radius: 6px; z-index: 1;"></div>
          <p style="font-size: 0.75rem; color: #888; margin: 0.25rem 0 0;">Klik pada peta untuk menentukan titik koordinat dan radius absensi.</p>
        </div>

        <!-- Footer actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
          <button @click="closeModal" class="btn btn-secondary">Batal</button>
          <button @click="saveItem" class="btn btn-primary" :disabled="isSaving">
            {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Atur Shift -->
    <div
      v-if="showShiftModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: flex-start; justify-content: center; z-index: 1000; padding: 1rem; overflow-y: auto;"
    >
      <div class="card" style="width: 100%; max-width: 800px; margin: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">Atur Shift: {{ selectedLocation?.NamaArea }}</h2>
          <button @click="closeShiftModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div style="display: flex; gap: 1.5rem;">
          <!-- Form Shift -->
          <div style="flex: 1;">
            <h3 style="margin-top: 0;">{{ isShiftEditing ? 'Edit Shift' : 'Tambah Shift' }}</h3>
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Nama Shift <span style="color:#e53e3e;">*</span></label>
              <input v-model="shiftForm.NamaShift" class="input" placeholder="Contoh: Shift 1" />
            </div>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
              <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Mulai Bekerja <span style="color:#e53e3e;">*</span></label>
                <input type="time" v-model="shiftForm.MulaiBekerja" class="input" />
              </div>
              <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Selesai Bekerja <span style="color:#e53e3e;">*</span></label>
                <input type="time" v-model="shiftForm.SelesaiBekerja" class="input" />
              </div>
            </div>
            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
              <input type="checkbox" v-model="shiftForm.GantiHari" id="gantihari" />
              <label for="gantihari" style="font-weight: 500; cursor: pointer;">Ganti Hari (Shift melewati tengah malam)</label>
            </div>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
              <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Mulai Absen (Menit)</label>
                <input type="number" v-model.number="shiftForm.MulaiAbsen" class="input" placeholder="Menit sebelum masuk" />
              </div>
              <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Max Absen (Menit)</label>
                <input type="number" v-model.number="shiftForm.MaxAbsen" class="input" placeholder="Menit setelah masuk" />
              </div>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
              <button v-if="isShiftEditing" @click="resetShiftForm" class="btn btn-secondary">Batal Edit</button>
              <button @click="saveShift" class="btn btn-primary" :disabled="isSavingShift">
                {{ isSavingShift ? 'Menyimpan...' : 'Simpan Shift' }}
              </button>
            </div>
          </div>
          
          <!-- Daftar Shift -->
          <div style="flex: 1; border-left: 1px solid #eee; padding-left: 1.5rem;">
            <h3 style="margin-top: 0;">Daftar Shift</h3>
            <LoadingSpinner v-if="isLoadingShifts" />
            <div v-else>
              <div v-if="shiftItems.length === 0" style="text-align: center; color: #888; padding: 2rem 0;">
                Belum ada shift untuk lokasi ini.
              </div>
              <div v-else style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 400px; overflow-y: auto;">
                <div v-for="shift in shiftItems" :key="shift.id" style="border: 1px solid #ddd; border-radius: 6px; padding: 0.75rem;">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                      <strong style="display: block; font-size: 1.1rem; color: #333;">{{ shift.NamaShift }}</strong>
                      <span style="font-size: 0.9rem; color: #555;">{{ shift.MulaiBekerja.substring(0, 5) }} - {{ shift.SelesaiBekerja.substring(0, 5) }}</span>
                      <span v-if="shift.GantiHari" class="badge" style="margin-left: 0.5rem; font-size: 0.7rem;">Ganti Hari</span>
                    </div>
                    <div style="display: flex; gap: 0.25rem;">
                      <button @click="editShift(shift)" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Edit</button>
                      <button @click="deleteShift(shift.id)" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                    </div>
                  </div>
                  <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #777;">
                    Absen: -{{ shift.MulaiAbsen }}m s/d +{{ shift.MaxAbsen }}m
                  </div>
                </div>
              </div>
            </div>
          </div>
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
  name: 'LokasiPatroli',
  components: { LoadingSpinner },

  data() {
    return {
      items: [],
      isLoading: false,
      isSaving: false,
      showModal: false,
      isEditing: false,
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      form: this.defaultForm(),

      // Map
      map: null,
      marker: null,
      circle: null,

      // Map search
      mapSearchQuery: '',
      searchResults: [],
      searchNoResult: false,
      isSearching: false,

      // Shift
      showShiftModal: false,
      shiftItems: [],
      isLoadingShifts: false,
      isSavingShift: false,
      isShiftEditing: false,
      selectedLocation: null,
      shiftForm: {
        id: null,
        NamaShift: '',
        MulaiBekerja: '',
        SelesaiBekerja: '',
        GantiHari: false,
        MulaiAbsen: 30,
        MaxAbsen: 120,
      },
    };
  },

  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(
        (i) =>
          i.NamaArea?.toLowerCase().includes(q) ||
          i.AlamatArea?.toLowerCase().includes(q) ||
          i.Keterangan?.toLowerCase().includes(q),
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
    this.fetchItems();
  },

  beforeUnmount() {
    this.destroyMap();
  },

  methods: {
    defaultForm() {
      return {
        id: null,
        NamaArea: '',
        AlamatArea: '',
        Keterangan: '',
        Latitude: '',
        Longitude: '',
        Radius: 100,
      };
    },

    defaultShiftForm() {
      return {
        id: null,
        NamaShift: '',
        MulaiBekerja: '',
        SelesaiBekerja: '',
        GantiHari: false,
        MulaiAbsen: 30,
        MaxAbsen: 120,
      };
    },

    async fetchItems() {
      this.isLoading = true;
      try {
        const res = await axios.get('/lokasi-patroli');
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
      this.mapSearchQuery = '';
      this.searchResults = [];
      this.searchNoResult = false;
      this.showModal = true;
      this.$nextTick(() => this.initMap());
    },

    editItem(item) {
      this.form = {
        id: item.id,
        NamaArea: item.NamaArea,
        AlamatArea: item.AlamatArea,
        Keterangan: item.Keterangan || '',
        Latitude: item.Latitude || '',
        Longitude: item.Longitude || '',
        Radius: parseInt(item.Radius) || 100,
      };
      this.isEditing = true;
      this.mapSearchQuery = '';
      this.searchResults = [];
      this.searchNoResult = false;
      this.showModal = true;
      this.$nextTick(() => this.initMap());
    },

    closeModal() {
      this.showModal = false;
      this.destroyMap();
    },

    async saveItem() {
      if (!this.form.NamaArea.trim()) {
        Swal.fire('Perhatian', 'Nama Lokasi tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.form.AlamatArea.trim()) {
        Swal.fire('Perhatian', 'Alamat tidak boleh kosong.', 'warning');
        return;
      }

      this.isSaving = true;
      try {
        if (this.isEditing) {
          await axios.put(`/lokasi-patroli/${this.form.id}`, this.form);
        } else {
          await axios.post('/lokasi-patroli', this.form);
        }
        this.closeModal();
        await this.fetchItems();
        Swal.fire('Berhasil!', 'Data lokasi patroli berhasil disimpan.', 'success');
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Terjadi kesalahan saat menyimpan.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async deleteItem(id) {
      const result = await Swal.fire({
        title: 'Yakin hapus lokasi ini?',
        text: 'Data lokasi patroli akan dihapus permanen.',
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
        await axios.delete(`/lokasi-patroli/${id}`);
        await this.fetchItems();
        Swal.fire('Dihapus!', 'Lokasi patroli berhasil dihapus.', 'success');
      } catch (e) {
        Swal.fire('Gagal', e.response?.data?.message || 'Gagal menghapus data.', 'error');
      }
    },

    // ===== SHIFT =====
    async openShiftModal(location) {
      this.selectedLocation = location;
      this.showShiftModal = true;
      this.resetShiftForm();
      await this.fetchShifts();
    },

    closeShiftModal() {
      this.showShiftModal = false;
      this.selectedLocation = null;
    },

    resetShiftForm() {
      this.shiftForm = this.defaultShiftForm();
      this.isShiftEditing = false;
    },

    async fetchShifts() {
      if (!this.selectedLocation) return;
      this.isLoadingShifts = true;
      try {
        const res = await axios.get('/tshift', { params: { LocationID: this.selectedLocation.id } });
        this.shiftItems = res.data.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.isLoadingShifts = false;
      }
    },

    editShift(shift) {
      this.shiftForm = {
        id: shift.id,
        NamaShift: shift.NamaShift,
        MulaiBekerja: shift.MulaiBekerja.substring(0, 5),
        SelesaiBekerja: shift.SelesaiBekerja.substring(0, 5),
        GantiHari: !!shift.GantiHari,
        MulaiAbsen: shift.MulaiAbsen,
        MaxAbsen: shift.MaxAbsen,
      };
      this.isShiftEditing = true;
    },

    async saveShift() {
      if (!this.shiftForm.NamaShift.trim()) {
        Swal.fire('Perhatian', 'Nama Shift tidak boleh kosong.', 'warning');
        return;
      }
      if (!this.shiftForm.MulaiBekerja || !this.shiftForm.SelesaiBekerja) {
        Swal.fire('Perhatian', 'Jam kerja tidak valid.', 'warning');
        return;
      }

      this.isSavingShift = true;
      try {
        const payload = {
          ...this.shiftForm,
          LocationID: this.selectedLocation.id,
          GantiHari: this.shiftForm.GantiHari ? 1 : 0,
        };

        if (this.isShiftEditing) {
          await axios.put(`/tshift/${this.shiftForm.id}`, payload);
        } else {
          await axios.post('/tshift', payload);
        }
        
        this.resetShiftForm();
        await this.fetchShifts();
        Swal.fire({ title: 'Berhasil!', text: 'Data shift berhasil disimpan.', icon: 'success', timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Terjadi kesalahan saat menyimpan shift.', 'error');
      } finally {
        this.isSavingShift = false;
      }
    },

    async deleteShift(id) {
      const result = await Swal.fire({
        title: 'Yakin hapus shift ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e53e3e',
      });
      if (!result.isConfirmed) return;

      try {
        await axios.delete(`/tshift/${id}`);
        await this.fetchShifts();
        Swal.fire({ title: 'Dihapus!', text: 'Shift berhasil dihapus.', icon: 'success', timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire('Gagal', e.response?.data?.message || 'Gagal menghapus shift.', 'error');
      }
    },

    // ===== MAP =====
    initMap() {
      if (typeof window.L === 'undefined') {
        this.loadLeaflet().then(() => this.buildMap());
      } else {
        this.buildMap();
      }
    },

    loadLeaflet() {
      return new Promise((resolve) => {
        if (document.getElementById('leaflet-css')) {
          resolve();
          return;
        }
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = resolve;
        document.head.appendChild(script);
      });
    },

    buildMap() {
      this.destroyMap();

      const lat = parseFloat(this.form.Latitude) || -6.2088;
      const lng = parseFloat(this.form.Longitude) || 106.8456;
      const radius = this.form.Radius || 100;
      const hasCoord = !isNaN(parseFloat(this.form.Latitude)) && !isNaN(parseFloat(this.form.Longitude));

      const L = window.L;
      this.map = L.map('lokasi-map').setView([lat, lng], 15);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(this.map);

      if (hasCoord) {
        this.placeMarker(lat, lng, radius);
      }

      this.map.on('click', (e) => {
        this.placeMarker(e.latlng.lat, e.latlng.lng, this.form.Radius);
      });
    },

    placeMarker(lat, lng, radius) {
      const L = window.L;
      if (this.marker) this.map.removeLayer(this.marker);
      if (this.circle) this.map.removeLayer(this.circle);

      this.marker = L.marker([lat, lng]).addTo(this.map);
      this.circle = L.circle([lat, lng], {
        radius,
        color: '#3182ce',
        fillColor: '#3182ce',
        fillOpacity: 0.12,
        weight: 2,
      }).addTo(this.map);

      this.form.Latitude = lat.toFixed(8);
      this.form.Longitude = lng.toFixed(8);
      this.map.setView([lat, lng], 15);
    },

    onCoordChange() {
      const lat = parseFloat(this.form.Latitude);
      const lng = parseFloat(this.form.Longitude);
      if (!isNaN(lat) && !isNaN(lng) && this.map) {
        this.placeMarker(lat, lng, this.form.Radius);
      }
    },

    onRadiusChange() {
      if (this.circle) {
        this.circle.setRadius(this.form.Radius);
      }
    },

    destroyMap() {
      if (this.map) {
        this.map.remove();
        this.map = null;
        this.marker = null;
        this.circle = null;
      }
    },

    // ===== MAP SEARCH (Nominatim) =====
    async doMapSearch() {
      const q = this.mapSearchQuery.trim();
      if (!q) return;

      this.isSearching = true;
      this.searchResults = [];
      this.searchNoResult = false;

      try {
        const res = await fetch(
          `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=6&addressdetails=1`,
          { headers: { 'Accept-Language': 'id' } },
        );
        const data = await res.json();

        if (!data || data.length === 0) {
          this.searchNoResult = true;
        } else {
          this.searchResults = data;
        }
      } catch {
        Swal.fire('Error', 'Gagal menghubungi layanan pencarian.', 'error');
      } finally {
        this.isSearching = false;
      }
    },

    selectSearchResult(result) {
      const lat = parseFloat(result.lat);
      const lng = parseFloat(result.lon);
      this.mapSearchQuery = result.display_name;
      this.searchResults = [];
      this.searchNoResult = false;
      if (this.map) {
        this.placeMarker(lat, lng, this.form.Radius);
      }
    },
  },
};
</script>