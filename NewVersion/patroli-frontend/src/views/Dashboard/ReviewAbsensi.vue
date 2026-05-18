<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Review Absensi</h1>
    </div>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tanggal Awal</label>
          <input type="date" v-model="filter.tgl_awal" class="input" />
        </div>

        <div style="flex: 1; min-width: 150px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tanggal Akhir</label>
          <input type="date" v-model="filter.tgl_akhir" class="input" />
        </div>

        <div style="flex: 2; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Lokasi</label>
          <select v-model="filter.location_id" class="input">
            <option value="">Semua Lokasi</option>
            <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
          </select>
        </div>

        <div style="flex: 1; min-width: 160px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tipe Tampilan</label>
          <div style="display: flex; gap: 0; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
            <button @click="filter.tipe = 'summary'" :style="filter.tipe === 'summary' ? activeTipeStyle : inactiveTipeStyle">Summary</button>
            <button @click="filter.tipe = 'detail'" :style="filter.tipe === 'detail' ? activeTipeStyle : inactiveTipeStyle">Detail</button>
          </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
          <button @click="fetchData" class="btn btn-primary" :disabled="isLoading">
            {{ isLoading ? 'Memuat...' : 'Tampilkan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Tabel Summary -->
    <div v-if="filter.tipe === 'summary'" class="card table-container" style="position: relative; min-height: 200px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
          <span style="font-weight: 600; color: #374151;">
            Data Summary Absensi
            <span v-if="summaryItems.length" style="font-weight: 400; color: #6b7280; margin-left: 0.5rem;">({{ summaryItems.length }} baris)</span>
          </span>
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <input v-model="searchQuery" placeholder="Cari NIK / Nama..." class="input" style="width: 200px;" />
            <button @click="exportSummaryExcel" class="btn btn-secondary" :disabled="!filteredSummary.length" style="white-space: nowrap;">&#128190; Excel</button>
            <button @click="exportSummaryPDF" class="btn btn-secondary" :disabled="!filteredSummary.length" style="white-space: nowrap;">&#128196; PDF</button>
          </div>
        </div>

        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th style="width: 40px;">No</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Jadwal</th>
                <th style="text-align: center;">Masuk</th>
                <th style="text-align: center;">OFF</th>
                <th style="text-align: center;">IZIN</th>
                <th style="text-align: center;">CUTI</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!hasSearched && summaryItems.length === 0">
                <td colspan="9" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data.
                </td>
              </tr>
              <tr v-else-if="paginatedSummary.length === 0">
                <td colspan="9" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada data ditemukan.</td>
              </tr>
              <tr v-for="(item, idx) in paginatedSummary" :key="idx">
                <td style="color: #9ca3af; font-size: 0.8rem;">{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="font-family: monospace; font-size: 0.875rem;">{{ item.NIK || '-' }}</td>
                <td>{{ item.NamaSecurity || '-' }}</td>
                <td style="font-size: 0.875rem; color: #4b5563;">
                  <span>{{ item.Jadwal || '-' }}</span>
                  <div v-if="item.TanpaJadwal > 0" style="font-size: 0.72rem; color: #f59e0b; margin-top: 0.15rem;">
                    &#9888; {{ item.TanpaJadwal }} hari tanpa jadwal
                  </div>
                </td>
                <td style="text-align: center;">
                  <span :style="item.Masuk > 0 ? 'display:inline-block;background:#dcfce7;color:#16a34a;border-radius:9999px;padding:0.1rem 0.65rem;font-size:0.8rem;font-weight:700;' : 'color:#9ca3af;font-size:0.875rem;'">
                    {{ item.Masuk }}
                  </span>
                </td>
                <td style="text-align: center;">
                  <span :style="item.OFF > 0 ? 'display:inline-block;background:#f3f4f6;color:#6b7280;border-radius:9999px;padding:0.1rem 0.65rem;font-size:0.8rem;font-weight:700;' : 'color:#9ca3af;font-size:0.875rem;'">
                    {{ item.OFF }}
                  </span>
                </td>
                <td style="text-align: center;">
                  <span :style="item.IZIN > 0 ? 'display:inline-block;background:#fef3c7;color:#d97706;border-radius:9999px;padding:0.1rem 0.65rem;font-size:0.8rem;font-weight:700;' : 'color:#9ca3af;font-size:0.875rem;'">
                    {{ item.IZIN }}
                  </span>
                </td>
                <td style="text-align: center;">
                  <span :style="item.CUTI > 0 ? 'display:inline-block;background:#dbeafe;color:#1d4ed8;border-radius:9999px;padding:0.1rem 0.65rem;font-size:0.8rem;font-weight:700;' : 'color:#9ca3af;font-size:0.875rem;'">
                    {{ item.CUTI }}
                  </span>
                </td>
                <td style="text-align: center;">
                  <button
                    @click="goToDetail(item)"
                    class="btn btn-secondary"
                    style="padding: 0.25rem 0.75rem; font-size: 0.8rem; white-space: nowrap;"
                  >&#128197; Detail</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="filteredSummary.length > 0" class="pagination-footer" style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>Show</span>
            <select v-model="itemsPerPage" class="input" style="width: auto;">
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
            <span>per halaman &mdash; Total: {{ filteredSummary.length }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary">&#8249;</button>
            <span>{{ currentPage }} / {{ totalPages || 1 }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++" class="btn btn-secondary">&#8250;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Detail -->
    <div v-if="filter.tipe === 'detail'" class="card table-container" style="position: relative; min-height: 200px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
          <span style="font-weight: 600; color: #374151;">
            Data Detail Absensi
            <span v-if="detailItems.length" style="font-weight: 400; color: #6b7280; margin-left: 0.5rem;">({{ detailItems.length }} baris)</span>
          </span>
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <input v-model="searchQuery" placeholder="Cari NIK / Nama..." class="input" style="width: 200px;" />
            <button @click="exportDetailExcel" class="btn btn-secondary" :disabled="!filteredDetail.length" style="white-space: nowrap;">&#128190; Excel</button>
            <button @click="exportDetailPDF" class="btn btn-secondary" :disabled="!filteredDetail.length" style="white-space: nowrap;">&#128196; PDF</button>
          </div>
        </div>

        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th style="width: 40px;">No</th>
                <th>Tanggal</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Jadwal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!hasSearched && detailItems.length === 0">
                <td colspan="10" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data.
                </td>
              </tr>
              <tr v-else-if="paginatedDetail.length === 0">
                <td colspan="10" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada data ditemukan.</td>
              </tr>
              <tr v-for="(item, idx) in paginatedDetail" :key="idx">
                <td style="color: #9ca3af; font-size: 0.8rem;">{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="white-space: nowrap; font-size: 0.875rem;">{{ formatDate(item.Tanggal) }}</td>
                <td style="font-family: monospace; font-size: 0.875rem;">{{ item.NIK || '-' }}</td>
                <td>{{ item.NamaSecurity || '-' }}</td>
                <td style="font-size: 0.875rem; color: #4b5563;">{{ item.Jadwal || '-' }}</td>
                <td style="font-size: 0.875rem; white-space: nowrap;">{{ formatTime(item.JamCheckin) }}</td>
                <td style="font-size: 0.875rem; white-space: nowrap;">{{ formatTime(item.JamCheckout) }}</td>
                <td style="font-size: 0.8rem; color: #4b5563;">{{ item.NamaLokasi || '-' }}</td>
                <td>
                  <span :style="statusBadgeStyle(item)">{{ statusLabel(item) }}</span>
                  <div v-if="statusKeterangan(item)" style="font-size: 0.72rem; color: #6b7280; margin-top: 0.2rem;">{{ statusKeterangan(item) }}</div>
                </td>
                <td style="text-align: center;">
                  <button
                    v-if="item.AbsensiID"
                    @click="viewDetail(item.AbsensiID)"
                    class="btn btn-secondary"
                    style="padding: 0.25rem 0.75rem; font-size: 0.8rem; white-space: nowrap;"
                  >View Detail</button>
                  <span v-else style="color: #d1d5db; font-size: 0.8rem;">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="filteredDetail.length > 0" class="pagination-footer" style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>Show</span>
            <select v-model="itemsPerPage" class="input" style="width: auto;">
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
            <span>per halaman &mdash; Total: {{ filteredDetail.length }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary">&#8249;</button>
            <span>{{ currentPage }} / {{ totalPages || 1 }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++" class="btn btn-secondary">&#8250;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal View Detail -->
    <div
      v-if="showDetailModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: flex-start; justify-content: center; z-index: 1200; padding: 1rem; overflow-y: auto;"
      @click.self="closeDetailModal"
    >
      <div class="card" style="width: 100%; max-width: 700px; margin: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
          <h2 class="page-title" style="margin: 0; font-size: 1.1rem;">&#128203; Detail Absensi</h2>
          <button @click="closeDetailModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1; color: #6b7280;">&times;</button>
        </div>

        <LoadingSpinner v-if="isLoadingDetail" />

        <div v-else-if="selectedDetail">
          <!-- Info Grid -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Tanggal</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ formatDate(selectedDetail.Tanggal) }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Lokasi</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ selectedDetail.NamaLokasi || '-' }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">NIK</div>
              <div style="font-weight: 600; font-size: 0.9rem; font-family: monospace;">{{ selectedDetail.NIK || '-' }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Nama Karyawan</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ selectedDetail.NamaSecurity || '-' }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Jadwal</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ selectedDetail.Jadwal || '-' }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Koordinat Checkin</div>
              <div style="font-weight: 500; font-size: 0.8rem; font-family: monospace; color: #374151;">
                {{ selectedDetail.KoordinatIN || 'Tidak tersedia' }}
              </div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Jam Masuk</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ formatTime(selectedDetail.JamCheckin) }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Jam Keluar</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ formatTime(selectedDetail.JamCheckout) }}</div>
            </div>
          </div>

          <!-- Jarak dari Lokasi -->
          <div
            v-if="selectedDetail.JarakMeter !== null"
            :style="jarakBadgeStyle(selectedDetail.JarakMeter)"
            style="border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;"
          >
            <span style="font-size: 1.2rem;">&#128207;</span>
            <div>
              <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem;">Jarak dari Lokasi</div>
              <div style="font-size: 1rem; font-weight: 700;">{{ formatJarak(selectedDetail.JarakMeter) }}</div>
            </div>
            <div v-if="selectedDetail.LokasiLatitude && selectedDetail.LokasiLongitude" style="margin-left: auto; font-size: 0.75rem; opacity: 0.8;">
              Radius lokasi: {{ getLokasiRadius() }} m
            </div>
          </div>

          <!-- Foto Absensi -->
          <div style="margin-bottom: 1.25rem;">
            <div style="font-weight: 600; font-size: 0.875rem; color: #374151; margin-bottom: 0.5rem;">&#128247; Foto Absensi</div>
            <div v-if="selectedDetail.ImageUrl" style="text-align: center; background: #f3f4f6; border-radius: 8px; padding: 0.5rem; overflow: hidden;">
              <img
                :src="selectedDetail.ImageUrl"
                alt="Foto Absensi"
                style="max-width: 100%; max-height: 320px; object-fit: contain; border-radius: 6px;"
                @error="onImageError"
              />
            </div>
            <div v-else style="background: #f3f4f6; border-radius: 8px; padding: 2rem; text-align: center; color: #9ca3af; font-size: 0.875rem;">
              Tidak ada foto
            </div>
          </div>

          <!-- Peta Koordinat -->
          <div style="margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <div style="font-weight: 600; font-size: 0.875rem; color: #374151;">&#127759; Titik Koordinat Checkin</div>
              <a
                v-if="detailLatLng"
                :href="googleMapsUrl"
                target="_blank"
                rel="noopener noreferrer"
                style="font-size: 0.8rem; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 0.3rem;"
              >
                &#x1F5FA; Buka di Google Maps
              </a>
            </div>
            <div v-if="detailLatLng">
              <div id="absensi-detail-map" style="height: 280px; border: 1px solid #d1d5db; border-radius: 8px; z-index: 1;"></div>
            </div>
            <div v-else style="background: #f3f4f6; border-radius: 8px; padding: 2rem; text-align: center; color: #9ca3af; font-size: 0.875rem;">
              Koordinat tidak tersedia
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end;">
            <button @click="closeDetailModal" class="btn btn-secondary">Tutup</button>
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
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

export default {
  name: 'ReviewAbsensi',
  components: { LoadingSpinner },

  data() {
    const today = new Date().toISOString().slice(0, 10);
    const firstOfMonth = today.slice(0, 8) + '01';
    return {
      filter: {
        tgl_awal: firstOfMonth,
        tgl_akhir: today,
        location_id: '',
        tipe: 'summary',
      },

      lokasiList: [],
      summaryItems: [],
      detailItems: [],
      hasSearched: false,

      isLoading: false,
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,

      showDetailModal: false,
      isLoadingDetail: false,
      selectedDetail: null,
      detailMap: null,
      detailMarker: null,
      lokasiMarker: null,
      jarakPolyline: null,

      activeTipeStyle: 'flex: 1; padding: 0.45rem 0; background: #2563eb; color: #fff; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 600;',
      inactiveTipeStyle: 'flex: 1; padding: 0.45rem 0; background: #fff; color: #374151; border: none; cursor: pointer; font-size: 0.875rem;',
    };
  },

  computed: {
    filteredSummary() {
      if (!this.searchQuery) return this.summaryItems;
      const q = this.searchQuery.toLowerCase();
      return this.summaryItems.filter(
        (i) => i.NIK?.toLowerCase().includes(q) || i.NamaSecurity?.toLowerCase().includes(q),
      );
    },

    filteredDetail() {
      if (!this.searchQuery) return this.detailItems;
      const q = this.searchQuery.toLowerCase();
      return this.detailItems.filter(
        (i) => i.NIK?.toLowerCase().includes(q) || i.NamaSecurity?.toLowerCase().includes(q),
      );
    },

    totalPages() {
      const src = this.filter.tipe === 'summary' ? this.filteredSummary : this.filteredDetail;
      return Math.ceil(src.length / this.itemsPerPage) || 1;
    },

    paginatedSummary() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredSummary.slice(start, start + this.itemsPerPage);
    },

    paginatedDetail() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredDetail.slice(start, start + this.itemsPerPage);
    },

    detailLatLng() {
      return this.parseKoordinat(this.selectedDetail?.KoordinatIN);
    },

    googleMapsUrl() {
      if (!this.detailLatLng) return '#';
      return `https://www.google.com/maps?q=${this.detailLatLng.lat},${this.detailLatLng.lng}`;
    },
  },

  watch: {
    searchQuery() {
      this.currentPage = 1;
    },
    'filter.tipe'() {
      this.searchQuery = '';
      this.currentPage = 1;
    },
  },

  mounted() {
    this.fetchLokasi();
  },

  beforeUnmount() {
    this.destroyDetailMap();
  },

  methods: {
    goToDetail(item) {
      this.$router.push({
        name: 'ReviewAbsensiKaryawan',
        query: {
          nik:         item.NIK,
          tgl_awal:    this.filter.tgl_awal,
          tgl_akhir:   this.filter.tgl_akhir,
          location_id: this.filter.location_id || '',
        },
      });
    },

    async fetchLokasi() {
      try {
        const res = await axios.get('/lokasi-patroli');
        this.lokasiList = res.data.data;
      } catch (e) {
        console.error('Gagal memuat lokasi:', e);
      }
    },

    async fetchData() {
      if (!this.filter.tgl_awal || !this.filter.tgl_akhir) {
        Swal.fire('Perhatian', 'Tanggal Awal dan Tanggal Akhir wajib diisi.', 'warning');
        return;
      }

      this.isLoading = true;
      this.searchQuery = '';
      this.currentPage = 1;
      this.hasSearched = true;

      const params = { tgl_awal: this.filter.tgl_awal, tgl_akhir: this.filter.tgl_akhir };
      if (this.filter.location_id) params.location_id = this.filter.location_id;

      try {
        const endpoint = this.filter.tipe === 'summary'
          ? '/review-absensi/summary'
          : '/review-absensi/detail';
        const res = await axios.get(endpoint, { params });

        if (this.filter.tipe === 'summary') {
          this.summaryItems = res.data.data;
        } else {
          this.detailItems = res.data.data;
        }
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data.', 'error');
      } finally {
        this.isLoading = false;
      }
    },

    async viewDetail(id) {
      this.showDetailModal = true;
      this.selectedDetail = null;
      this.isLoadingDetail = true;
      this.destroyDetailMap();

      try {
        const res = await axios.get(`/review-absensi/record/${id}`);
        this.selectedDetail = res.data.data;

        if (this.detailLatLng) {
          this.$nextTick(() => this.initDetailMap());
        }
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat detail.', 'error');
        this.showDetailModal = false;
      } finally {
        this.isLoadingDetail = false;
      }
    },

    closeDetailModal() {
      this.showDetailModal = false;
      this.selectedDetail = null;
      this.destroyDetailMap();
    },

    parseKoordinat(koordinat) {
      if (!koordinat) return null;
      const parts = String(koordinat).split(',');
      if (parts.length < 2) return null;
      const lat = parseFloat(parts[0].trim());
      const lng = parseFloat(parts[1].trim());
      if (isNaN(lat) || isNaN(lng)) return null;
      return { lat, lng };
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      const [y, m, d] = String(dateStr).split('-');
      return `${d}/${m}/${y}`;
    },

    formatTime(timeStr) {
      if (!timeStr || timeStr === '00:00:00') return '-';
      // Jika berupa datetime (mis. "2023-09-06 16:11:38"), ambil bagian waktu saja
      const str = String(timeStr);
      if (str.includes(' ')) return str.split(' ')[1].slice(0, 5);
      return str.slice(0, 5);
    },

    formatJarak(meter) {
      if (meter === null || meter === undefined) return '-';
      if (meter >= 1000) return `${(meter / 1000).toFixed(2)} km`;
      return `${meter} m`;
    },

    jarakBadgeStyle(meter) {
      if (meter === null) return '';
      // Ambil radius dari lokasi untuk perbandingan warna (default 50m)
      const lokasiData = this.lokasiList.find(
        (l) => l.NamaArea === this.selectedDetail?.NamaLokasi,
      );
      const radius = lokasiData?.Radius || 50;

      if (meter <= radius) {
        return 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;';
      } else if (meter <= radius * 2) {
        return 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
      }
      return 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;';
    },

    getLokasiRadius() {
      const lokasiData = this.lokasiList.find(
        (l) => l.NamaArea === this.selectedDetail?.NamaLokasi,
      );
      return lokasiData?.Radius || '-';
    },

    initDetailMap() {
      if (typeof window.L === 'undefined') {
        this.loadLeaflet().then(() => this.buildDetailMap());
      } else {
        this.buildDetailMap();
      }
    },

    loadLeaflet() {
      return new Promise((resolve) => {
        if (document.getElementById('leaflet-css')) { resolve(); return; }
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

    buildDetailMap() {
      if (!this.detailLatLng) return;
      this.destroyDetailMap();

      const { lat, lng } = this.detailLatLng;
      const L = window.L;
      const mapEl = document.getElementById('absensi-detail-map');
      if (!mapEl) return;

      this.detailMap = L.map('absensi-detail-map').setView([lat, lng], 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(this.detailMap);

      // Marker titik checkin
      const checkinIcon = L.divIcon({
        html: `<div style="background:#2563eb;color:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:16px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.35);">&#128100;</div>`,
        className: '',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -20],
      });

      this.detailMarker = L.marker([lat, lng], { icon: checkinIcon })
        .addTo(this.detailMap)
        .bindPopup(
          `<strong>Titik Checkin</strong><br>` +
          `${this.selectedDetail.NamaSecurity || this.selectedDetail.NIK}<br>` +
          `${lat.toFixed(6)}, ${lng.toFixed(6)}<br>` +
          `<a href="${this.googleMapsUrl}" target="_blank" rel="noopener">Buka Google Maps</a>`,
        )
        .openPopup();

      // Marker titik lokasi (jika ada koordinat lokasi)
      const lokasiLat = parseFloat(this.selectedDetail.LokasiLatitude);
      const lokasiLng = parseFloat(this.selectedDetail.LokasiLongitude);

      if (!isNaN(lokasiLat) && !isNaN(lokasiLng)) {
        const lokasiIcon = L.divIcon({
          html: `<div style="background:#dc2626;color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.35);">&#127968;</div>`,
          className: '',
          iconSize: [28, 28],
          iconAnchor: [14, 14],
          popupAnchor: [0, -18],
        });

        this.lokasiMarker = L.marker([lokasiLat, lokasiLng], { icon: lokasiIcon })
          .addTo(this.detailMap)
          .bindPopup(`<strong>Titik Lokasi</strong><br>${this.selectedDetail.NamaLokasi || '-'}`);

        // Garis antara lokasi dan checkin
        this.jarakPolyline = L.polyline([[lokasiLat, lokasiLng], [lat, lng]], {
          color: '#f59e0b',
          weight: 2,
          dashArray: '5,5',
          opacity: 0.8,
        }).addTo(this.detailMap);

        // Fit map agar kedua marker terlihat
        this.detailMap.fitBounds([[lokasiLat, lokasiLng], [lat, lng]], { padding: [40, 40] });
      }
    },

    destroyDetailMap() {
      if (this.detailMap) {
        this.detailMap.remove();
        this.detailMap = null;
        this.detailMarker = null;
        this.lokasiMarker = null;
        this.jarakPolyline = null;
      }
    },

    onImageError(e) {
      e.target.style.display = 'none';
      e.target.parentElement.innerHTML =
        '<div style="color:#9ca3af;padding:2rem;text-align:center;font-size:0.875rem;">Foto tidak dapat dimuat</div>';
    },

    exportTitle() {
      const lokasi = this.filter.location_id
        ? (this.lokasiList.find((l) => l.id == this.filter.location_id)?.NamaArea || 'Semua Lokasi')
        : 'Semua Lokasi';
      return `Review Absensi | ${this.filter.tgl_awal} s/d ${this.filter.tgl_akhir} | ${lokasi}`;
    },

    exportFileName(suffix) {
      return `review_absensi_${suffix}_${this.filter.tgl_awal}_${this.filter.tgl_akhir}`;
    },

    statusLabel(item) {
      if (item.Masuk == 1) return 'Masuk';
      if (item.IsOFF == 1) return 'OFF';
      if (item.KeteranganIZIN != null) return 'IZIN';
      if (item.KeteranganCUTI != null) return 'CUTI';
      return 'Absen';
    },

    statusKeterangan(item) {
      if (item.KeteranganIZIN) return item.KeteranganIZIN;
      if (item.KeteranganCUTI) return item.KeteranganCUTI;
      return null;
    },

    statusBadgeStyle(item) {
      const base = 'display:inline-block;border-radius:9999px;padding:0.1rem 0.65rem;font-size:0.75rem;font-weight:600;white-space:nowrap;';
      if (item.Masuk == 1) return base + 'background:#dcfce7;color:#16a34a;';
      if (item.IsOFF == 1) return base + 'background:#f3f4f6;color:#6b7280;';
      if (item.KeteranganIZIN != null) return base + 'background:#fef3c7;color:#d97706;';
      if (item.KeteranganCUTI != null) return base + 'background:#dbeafe;color:#1d4ed8;';
      return base + 'background:#fee2e2;color:#dc2626;';
    },

    exportSummaryExcel() {
      const rows = this.filteredSummary.map((item, idx) => ({
        'No': idx + 1,
        'NIK': item.NIK || '-',
        'Nama Karyawan': item.NamaSecurity || '-',
        'Jadwal': item.Jadwal || '-',
        'Masuk': item.Masuk,
        'OFF': item.OFF,
        'IZIN': item.IZIN,
        'CUTI': item.CUTI,
        'Tanpa Jadwal': item.TanpaJadwal || 0,
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [
        { wch: 5 }, { wch: 16 }, { wch: 28 }, { wch: 20 },
        { wch: 8 }, { wch: 8 }, { wch: 8 }, { wch: 8 }, { wch: 14 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Summary Absensi');
      XLSX.writeFile(wb, `${this.exportFileName('summary')}.xlsx`);
    },

    exportSummaryPDF() {
      const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.text('Review Absensi — Summary', 14, 16);
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text(this.exportTitle(), 14, 22);

      autoTable(doc, {
        startY: 28,
        head: [['No', 'NIK', 'Nama Karyawan', 'Jadwal', 'Masuk', 'OFF', 'IZIN', 'CUTI']],
        body: this.filteredSummary.map((item, idx) => [
          idx + 1,
          item.NIK || '-',
          item.NamaSecurity || '-',
          item.Jadwal || '-',
          item.Masuk,
          item.OFF,
          item.IZIN,
          item.CUTI,
        ]),
        headStyles: { fillColor: [37, 99, 235], fontSize: 9 },
        bodyStyles: { fontSize: 8.5 },
        columnStyles: {
          0: { cellWidth: 10, halign: 'center' },
          4: { cellWidth: 14, halign: 'center' },
          5: { cellWidth: 12, halign: 'center' },
          6: { cellWidth: 12, halign: 'center' },
          7: { cellWidth: 12, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 250, 252] },
      });

      doc.save(`${this.exportFileName('summary')}.pdf`);
    },

    exportDetailExcel() {
      const rows = this.filteredDetail.map((item, idx) => {
        const ket = this.statusKeterangan(item);
        return {
          'No': idx + 1,
          'Tanggal': this.formatDate(item.Tanggal),
          'NIK': item.NIK || '-',
          'Nama Karyawan': item.NamaSecurity || '-',
          'Jadwal': item.Jadwal || '-',
          'Jam Masuk': this.formatTime(item.JamCheckin),
          'Jam Keluar': this.formatTime(item.JamCheckout),
          'Lokasi': item.NamaLokasi || '-',
          'Status': ket ? `${this.statusLabel(item)} - ${ket}` : this.statusLabel(item),
        };
      });

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [
        { wch: 5 }, { wch: 14 }, { wch: 16 }, { wch: 28 },
        { wch: 18 }, { wch: 12 }, { wch: 12 }, { wch: 22 }, { wch: 30 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Detail Absensi');
      XLSX.writeFile(wb, `${this.exportFileName('detail')}.xlsx`);
    },

    exportDetailPDF() {
      const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.text('Review Absensi — Detail', 14, 16);
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text(this.exportTitle(), 14, 22);

      autoTable(doc, {
        startY: 28,
        head: [['No', 'Tanggal', 'NIK', 'Nama Karyawan', 'Jadwal', 'Jam Masuk', 'Jam Keluar', 'Lokasi', 'Status']],
        body: this.filteredDetail.map((item, idx) => {
          const ket = this.statusKeterangan(item);
          return [
            idx + 1,
            this.formatDate(item.Tanggal),
            item.NIK || '-',
            item.NamaSecurity || '-',
            item.Jadwal || '-',
            this.formatTime(item.JamCheckin),
            this.formatTime(item.JamCheckout),
            item.NamaLokasi || '-',
            ket ? `${this.statusLabel(item)} - ${ket}` : this.statusLabel(item),
          ];
        }),
        headStyles: { fillColor: [37, 99, 235], fontSize: 8 },
        bodyStyles: { fontSize: 7.5 },
        columnStyles: { 0: { cellWidth: 8, halign: 'center' } },
        alternateRowStyles: { fillColor: [248, 250, 252] },
      });

      doc.save(`${this.exportFileName('detail')}.pdf`);
    },
  },
};
</script>