<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Review Patroli</h1>
    </div>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <!-- Tanggal Awal -->
        <div style="flex: 1; min-width: 150px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tanggal Awal</label>
          <input type="date" v-model="filter.tgl_awal" class="input" />
        </div>

        <!-- Tanggal Akhir -->
        <div style="flex: 1; min-width: 150px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tanggal Akhir</label>
          <input type="date" v-model="filter.tgl_akhir" class="input" />
        </div>

        <!-- Lokasi -->
        <div style="flex: 2; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Lokasi</label>
          <select v-model="filter.location_id" class="input">
            <option value="">Semua Lokasi</option>
            <option v-for="lok in lokasiList" :key="lok.id" :value="lok.id">{{ lok.NamaArea }}</option>
          </select>
        </div>

        <!-- Tipe -->
        <div style="flex: 1; min-width: 160px;">
          <label style="display: block; margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem;">Tipe Tampilan</label>
          <div style="display: flex; gap: 0; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
            <button
              @click="filter.tipe = 'summary'"
              :style="filter.tipe === 'summary' ? activeTipeStyle : inactiveTipeStyle"
            >Summary</button>
            <button
              @click="filter.tipe = 'detail'"
              :style="filter.tipe === 'detail' ? activeTipeStyle : inactiveTipeStyle"
            >Detail</button>
          </div>
        </div>

        <!-- Tombol Tampilkan -->
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
            Data Summary Patroli
            <span v-if="summaryItems.length" style="font-weight: 400; color: #6b7280; margin-left: 0.5rem;">({{ summaryItems.length }} baris)</span>
          </span>
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <input v-model="searchQuery" placeholder="Cari NIK / Nama..." class="input" style="width: 200px;" />
            <button @click="exportSummaryExcel" class="btn btn-secondary" :disabled="!filteredSummary.length" style="white-space: nowrap;">
              &#128190; Excel
            </button>
            <button @click="exportSummaryPDF" class="btn btn-secondary" :disabled="!filteredSummary.length" style="white-space: nowrap;">
              &#128196; PDF
            </button>
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
                <th style="text-align: center;">Jumlah Patroli</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!hasSearched && summaryItems.length === 0">
                <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data.
                </td>
              </tr>
              <tr v-else-if="paginatedSummary.length === 0">
                <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Tidak ada data ditemukan.
                </td>
              </tr>
              <tr v-for="(item, idx) in paginatedSummary" :key="idx">
                <td style="color: #9ca3af; font-size: 0.8rem;">{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td>{{ formatDate(item.Tanggal) }}</td>
                <td style="font-family: monospace;">{{ item.NIK || '-' }}</td>
                <td>{{ item.NamaSecurity || '-' }}</td>
                <td style="text-align: center;">
                  <span class="badge">{{ item.JumlahPatroli }}</span>
                </td>
                <td style="text-align: center;">
                  <button
                    @click="openKunjungan(item)"
                    class="btn btn-secondary"
                    style="padding: 0.25rem 0.75rem; font-size: 0.8rem; white-space: nowrap;"
                  >
                    &#x1F5FA; Detail Kunjungan
                  </button>
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
            <span>per halaman — Total: {{ filteredSummary.length }}</span>
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
            Data Detail Patroli
            <span v-if="detailItems.length" style="font-weight: 400; color: #6b7280; margin-left: 0.5rem;">({{ detailItems.length }} record)</span>
          </span>
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <input v-model="searchQuery" placeholder="Cari NIK / Nama / Checkpoint..." class="input" style="width: 230px;" />
            <button @click="exportDetailExcel" class="btn btn-secondary" :disabled="!filteredDetail.length" style="white-space: nowrap;">
              &#128190; Excel
            </button>
            <button @click="exportDetailPDF" class="btn btn-secondary" :disabled="!filteredDetail.length" style="white-space: nowrap;">
              &#128196; PDF
            </button>
          </div>
        </div>

        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th style="width: 40px;">No</th>
                <th>Tanggal &amp; Jam</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>CheckPoint</th>
                <th>Koordinat</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!hasSearched && detailItems.length === 0">
                <td colspan="7" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data.
                </td>
              </tr>
              <tr v-else-if="paginatedDetail.length === 0">
                <td colspan="7" style="text-align: center; color: #9ca3af; padding: 2rem;">
                  Tidak ada data ditemukan.
                </td>
              </tr>
              <tr v-for="(item, idx) in paginatedDetail" :key="item.id">
                <td style="color: #9ca3af; font-size: 0.8rem;">{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="white-space: nowrap; font-size: 0.875rem;">{{ item.TanggalJam }}</td>
                <td style="font-family: monospace; font-size: 0.875rem;">{{ item.NIK || '-' }}</td>
                <td>{{ item.NamaSecurity || '-' }}</td>
                <td>{{ item.NamaCheckPoint || '-' }}</td>
                <td style="font-size: 0.8rem; color: #6b7280;">
                  <span v-if="item.Koordinat">{{ item.Koordinat }}</span>
                  <span v-else style="color: #d1d5db;">-</span>
                </td>
                <td style="text-align: center;">
                  <button
                    @click="viewDetail(item.id)"
                    class="btn btn-secondary"
                    style="padding: 0.25rem 0.75rem; font-size: 0.8rem; white-space: nowrap;"
                  >
                    View Detail
                  </button>
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
            <span>per halaman — Total: {{ filteredDetail.length }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button :disabled="currentPage === 1" @click="currentPage--" class="btn btn-secondary">&#8249;</button>
            <span>{{ currentPage }} / {{ totalPages || 1 }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++" class="btn btn-secondary">&#8250;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail Kunjungan -->
    <div
      v-if="showKunjunganModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); display: flex; align-items: center; justify-content: center; z-index: 1050; padding: 1rem;"
      @click.self="closeKunjunganModal"
    >
      <div class="card" style="width: 100%; max-width: 960px; height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden;">

        <!-- Header -->
        <div style="padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; flex-shrink: 0;">
          <div>
            <div style="font-weight: 700; font-size: 1rem; color: #111827;">&#x1F5FA; Detail Kunjungan Patroli</div>
            <div style="font-size: 0.8rem; color: #6b7280; margin-top: 0.15rem;" v-if="kunjunganInfo">
              {{ kunjunganInfo.NamaSecurity || kunjunganInfo.NIK }} &mdash; {{ formatDate(kunjunganInfo.Tanggal) }}
              <span v-if="kunjunganPoints.length" style="margin-left: 0.5rem;">({{ kunjunganPoints.length }} titik)</span>
            </div>
          </div>
          <button @click="closeKunjunganModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1; color: #6b7280;">&times;</button>
        </div>

        <!-- Loading -->
        <div v-if="isLoadingKunjungan" style="flex: 1; display: flex; align-items: center; justify-content: center;">
          <LoadingSpinner />
        </div>

        <template v-else>
          <!-- Map -->
          <div style="flex: 1; min-height: 0; position: relative;">
            <div id="kunjungan-map" style="width: 100%; height: 100%; z-index: 1;"></div>
            <!-- Legend overlay -->
            <div
              v-if="kunjunganPoints.length"
              style="position: absolute; top: 10px; right: 10px; z-index: 999; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 0.6rem 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.15); max-height: 200px; overflow-y: auto; min-width: 180px;"
            >
              <div style="font-weight: 700; font-size: 0.75rem; color: #374151; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em;">Urutan Kunjungan</div>
              <div
                v-for="(pt, idx) in kunjunganPoints"
                :key="pt.id"
                @click="focusKunjunganPoint(idx)"
                style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.3rem; border-radius: 4px; cursor: pointer; margin-bottom: 0.15rem;"
                :style="{ background: hoveredLegend === idx ? '#eff6ff' : 'transparent' }"
                @mouseenter="hoveredLegend = idx"
                @mouseleave="hoveredLegend = null"
              >
                <span :style="markerBadgeStyle(idx + 1)">{{ idx + 1 }}</span>
                <div>
                  <div style="font-size: 0.75rem; font-weight: 600; color: #374151; line-height: 1.2;">{{ pt.NamaCheckPoint || '-' }}</div>
                  <div style="font-size: 0.7rem; color: #9ca3af;">{{ pt.TanggalJam ? pt.TanggalJam.slice(11) : '' }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer: titik tanpa koordinat -->
          <div
            v-if="kunjunganNoCoord.length"
            style="flex-shrink: 0; padding: 0.6rem 1.25rem; background: #fffbeb; border-top: 1px solid #fde68a; font-size: 0.8rem; color: #92400e;"
          >
            <strong>{{ kunjunganNoCoord.length }} titik tanpa koordinat:</strong>
            {{ kunjunganNoCoord.map(p => p.NamaCheckPoint || p.NIK).join(', ') }}
          </div>
        </template>
      </div>
    </div>

    <!-- Modal View Detail -->
    <div
      v-if="showDetailModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: flex-start; justify-content: center; z-index: 1200; padding: 1rem; overflow-y: auto;"
      @click.self="closeDetailModal"
    >
      <div class="card" style="width: 100%; max-width: 680px; margin: auto;">
        <!-- Header Modal -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
          <h2 class="page-title" style="margin: 0; font-size: 1.1rem;">Detail Patroli</h2>
          <button @click="closeDetailModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1; color: #6b7280;">&times;</button>
        </div>

        <LoadingSpinner v-if="isLoadingDetail" />

        <div v-else-if="selectedDetail">
          <!-- Info Grid -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Tanggal &amp; Jam</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ selectedDetail.TanggalJam }}</div>
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
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">CheckPoint</div>
              <div style="font-weight: 600; font-size: 0.9rem;">{{ selectedDetail.NamaCheckPoint || '-' }}</div>
            </div>
            <div style="background: #f9fafb; border-radius: 6px; padding: 0.75rem;">
              <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.2rem;">Koordinat</div>
              <div style="font-weight: 600; font-size: 0.9rem; font-family: monospace; font-size: 0.8rem;">
                <span v-if="selectedDetail.Koordinat">{{ selectedDetail.Koordinat }}</span>
                <span v-else style="color: #d1d5db; font-weight: 400;">Tidak tersedia</span>
              </div>
            </div>
          </div>

          <!-- Catatan -->
          <div v-if="selectedDetail.Catatan" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.75rem; margin-bottom: 1.25rem;">
            <div style="font-size: 0.75rem; color: #92400e; margin-bottom: 0.25rem; font-weight: 600;">Catatan</div>
            <div style="font-size: 0.875rem; color: #78350f;">{{ selectedDetail.Catatan }}</div>
          </div>

          <!-- Gambar Patroli -->
          <div style="margin-bottom: 1.25rem;">
            <div style="font-weight: 600; font-size: 0.875rem; color: #374151; margin-bottom: 0.5rem;">Foto Patroli</div>
            <div v-if="selectedDetail.ImageUrl" style="text-align: center; background: #f3f4f6; border-radius: 8px; padding: 0.5rem; overflow: hidden;">
              <img
                :src="selectedDetail.ImageUrl"
                alt="Foto Patroli"
                style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 6px;"
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
              <div style="font-weight: 600; font-size: 0.875rem; color: #374151;">Titik Koordinat</div>
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
              <div id="detail-map" style="height: 260px; border: 1px solid #d1d5db; border-radius: 8px; z-index: 1;"></div>
            </div>
            <div v-else style="background: #f3f4f6; border-radius: 8px; padding: 2rem; text-align: center; color: #9ca3af; font-size: 0.875rem;">
              Koordinat tidak tersedia
            </div>
          </div>

          <!-- Tutup -->
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

const LEGACY_ASSET_URL = import.meta.env.VITE_LEGACY_ASSET_URL || 'http://localhost/patrolix86';

export default {
  name: 'ReviewPatroli',
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

      // Modal detail
      showDetailModal: false,
      isLoadingDetail: false,
      selectedDetail: null,

      // Modal kunjungan
      showKunjunganModal: false,
      isLoadingKunjungan: false,
      kunjunganInfo: null,
      kunjunganPoints: [],
      kunjunganNoCoord: [],
      hoveredLegend: null,

      // Map kunjungan
      kunjunganMap: null,
      kunjunganMarkers: [],
      kunjunganPolyline: null,

      // Map detail
      detailMap: null,
      detailMarker: null,

      // Styles
      activeTipeStyle: 'flex: 1; padding: 0.45rem 0; background: #2563eb; color: #fff; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 600;',
      inactiveTipeStyle: 'flex: 1; padding: 0.45rem 0; background: #fff; color: #374151; border: none; cursor: pointer; font-size: 0.875rem;',
    };
  },

  computed: {
    // Summary
    filteredSummary() {
      if (!this.searchQuery) return this.summaryItems;
      const q = this.searchQuery.toLowerCase();
      return this.summaryItems.filter(
        (i) =>
          i.NIK?.toLowerCase().includes(q) ||
          i.NamaSecurity?.toLowerCase().includes(q),
      );
    },

    // Detail
    filteredDetail() {
      if (!this.searchQuery) return this.detailItems;
      const q = this.searchQuery.toLowerCase();
      return this.detailItems.filter(
        (i) =>
          i.NIK?.toLowerCase().includes(q) ||
          i.NamaSecurity?.toLowerCase().includes(q) ||
          i.NamaCheckPoint?.toLowerCase().includes(q),
      );
    },

    totalPages() {
      const source = this.filter.tipe === 'summary' ? this.filteredSummary : this.filteredDetail;
      return Math.ceil(source.length / this.itemsPerPage) || 1;
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
      return this.parseKoordinat(this.selectedDetail?.Koordinat);
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
    this.destroyKunjunganMap();
    this.destroyDetailMap();
  },

  methods: {
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

      const params = {
        tgl_awal: this.filter.tgl_awal,
        tgl_akhir: this.filter.tgl_akhir,
      };
      if (this.filter.location_id) {
        params.location_id = this.filter.location_id;
      }

      try {
        if (this.filter.tipe === 'summary') {
          const res = await axios.get('/review-patroli/summary', { params });
          this.summaryItems = res.data.data;
        } else {
          const res = await axios.get('/review-patroli/detail', { params });
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
        const res = await axios.get(`/review-patroli/detail/${id}`);
        const data = res.data.data;

        // Tambahkan ImageUrl jika belum ada dari backend
        if (data.Image && !data.ImageUrl) {
          data.ImageUrl = `${LEGACY_ASSET_URL}/Assets/images/patroli/${data.Image}`;
        }

        this.selectedDetail = data;

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

    // ===== Modal Kunjungan =====
    async openKunjungan(item) {
      this.showKunjunganModal = true;
      this.isLoadingKunjungan = true;
      this.kunjunganInfo = item;
      this.kunjunganPoints = [];
      this.kunjunganNoCoord = [];
      this.hoveredLegend = null;
      this.destroyKunjunganMap();

      const params = {
        tgl_awal: item.Tanggal,
        tgl_akhir: item.Tanggal,
        kode_karyawan: item.NIK,
      };
      if (this.filter.location_id) {
        params.location_id = this.filter.location_id;
      }

      try {
        const res = await axios.get('/review-patroli/detail', { params });
        const allData = res.data.data || [];

        const withCoord = [];
        const noCoord = [];

        allData.forEach((d) => {
          const latlng = this.parseKoordinat(d.Koordinat);
          if (latlng) {
            withCoord.push({ ...d, _lat: latlng.lat, _lng: latlng.lng });
          } else {
            noCoord.push(d);
          }
        });

        this.kunjunganPoints = withCoord;
        this.kunjunganNoCoord = noCoord;

        if (withCoord.length === 0 && noCoord.length === 0) {
          Swal.fire('Info', 'Tidak ada data patroli untuk hari ini.', 'info');
          this.showKunjunganModal = false;
          return;
        }

        this.$nextTick(() => this.initKunjunganMap());
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data kunjungan.', 'error');
        this.showKunjunganModal = false;
      } finally {
        this.isLoadingKunjungan = false;
      }
    },

    closeKunjunganModal() {
      this.showKunjunganModal = false;
      this.kunjunganInfo = null;
      this.kunjunganPoints = [];
      this.kunjunganNoCoord = [];
      this.destroyKunjunganMap();
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

    markerBadgeStyle(num) {
      const hue = Math.min(num - 1, 9);
      const colors = [
        '#2563eb', '#0891b2', '#059669', '#d97706', '#dc2626',
        '#7c3aed', '#db2777', '#0284c7', '#16a34a', '#ca8a04',
      ];
      const bg = colors[hue] || '#2563eb';
      return `
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; border-radius: 50%;
        background: ${bg}; color: #fff;
        font-size: 0.65rem; font-weight: 700; flex-shrink: 0;
      `;
    },

    initKunjunganMap() {
      if (typeof window.L === 'undefined') {
        this.loadLeaflet().then(() => this.buildKunjunganMap());
      } else {
        this.buildKunjunganMap();
      }
    },

    buildKunjunganMap() {
      const pts = this.kunjunganPoints;
      if (!pts.length) return;

      this.destroyKunjunganMap();

      const mapEl = document.getElementById('kunjungan-map');
      if (!mapEl) return;

      const L = window.L;
      const center = [pts[0]._lat, pts[0]._lng];
      this.kunjunganMap = L.map('kunjungan-map').setView(center, 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(this.kunjunganMap);

      const markerColors = [
        '#2563eb', '#0891b2', '#059669', '#d97706', '#dc2626',
        '#7c3aed', '#db2777', '#0284c7', '#16a34a', '#ca8a04',
      ];

      const latlngs = [];

      pts.forEach((pt, idx) => {
        const num = idx + 1;
        const color = markerColors[(idx) % markerColors.length];
        const latlng = [pt._lat, pt._lng];
        latlngs.push(latlng);

        const icon = L.divIcon({
          html: `
            <div style="
              background:${color};color:#fff;border-radius:50%;
              width:28px;height:28px;
              display:flex;align-items:center;justify-content:center;
              font-weight:700;font-size:13px;
              border:2px solid #fff;
              box-shadow:0 2px 6px rgba(0,0,0,0.35);
            ">${num}</div>`,
          className: '',
          iconSize: [28, 28],
          iconAnchor: [14, 14],
          popupAnchor: [0, -16],
        });

        const marker = L.marker(latlng, { icon }).addTo(this.kunjunganMap);

        const gmUrl = `https://www.google.com/maps?q=${pt._lat},${pt._lng}`;
        marker.bindPopup(`
          <div style="min-width:160px">
            <div style="font-weight:700;font-size:0.85rem;margin-bottom:4px;">#${num} ${pt.NamaCheckPoint || 'Checkpoint'}</div>
            <div style="font-size:0.75rem;color:#555;margin-bottom:2px;">${pt.TanggalJam || ''}</div>
            <div style="font-size:0.72rem;color:#888;margin-bottom:6px;">${pt._lat.toFixed(6)}, ${pt._lng.toFixed(6)}</div>
            <button
              onclick="document.dispatchEvent(new CustomEvent('kunjungan-view-detail',{detail:'${pt.id}'}))"
              style="background:#2563eb;color:#fff;border:none;border-radius:4px;padding:3px 10px;font-size:0.75rem;cursor:pointer;margin-right:4px;"
            >Lihat Detail</button>
            <a href="${gmUrl}" target="_blank" rel="noopener"
              style="font-size:0.72rem;color:#2563eb;">Google Maps</a>
          </div>
        `);

        this.kunjunganMarkers.push(marker);
      });

      // Polyline connecting all points in order
      this.kunjunganPolyline = L.polyline(latlngs, {
        color: '#2563eb',
        weight: 2.5,
        opacity: 0.75,
        dashArray: '6,4',
      }).addTo(this.kunjunganMap);

      // Fit map to all markers
      this.kunjunganMap.fitBounds(L.latLngBounds(latlngs), { padding: [30, 30] });

      // Listen for popup "Lihat Detail" button click
      this._kunjunganDetailListener = (e) => {
        const id = parseInt(e.detail);
        if (id) this.viewDetail(id);
      };
      document.addEventListener('kunjungan-view-detail', this._kunjunganDetailListener);
    },

    focusKunjunganPoint(idx) {
      const marker = this.kunjunganMarkers[idx];
      if (marker && this.kunjunganMap) {
        this.kunjunganMap.setView(marker.getLatLng(), 18, { animate: true });
        marker.openPopup();
      }
    },

    destroyKunjunganMap() {
      if (this._kunjunganDetailListener) {
        document.removeEventListener('kunjungan-view-detail', this._kunjunganDetailListener);
        this._kunjunganDetailListener = null;
      }
      if (this.kunjunganMap) {
        this.kunjunganMap.remove();
        this.kunjunganMap = null;
      }
      this.kunjunganMarkers = [];
      this.kunjunganPolyline = null;
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      const [y, m, d] = dateStr.split('-');
      return `${d}/${m}/${y}`;
    },

    exportTitle() {
      const lokasi = this.filter.location_id
        ? (this.lokasiList.find((l) => l.id == this.filter.location_id)?.NamaArea || 'Semua Lokasi')
        : 'Semua Lokasi';
      return `Review Patroli | ${this.filter.tgl_awal} s/d ${this.filter.tgl_akhir} | ${lokasi}`;
    },

    exportFileName(suffix) {
      return `review_patroli_${suffix}_${this.filter.tgl_awal}_${this.filter.tgl_akhir}`;
    },

    // ===== Export Summary =====
    exportSummaryExcel() {
      const rows = this.filteredSummary.map((item, idx) => ({
        'No': idx + 1,
        'Tanggal': this.formatDate(item.Tanggal),
        'NIK': item.NIK || '-',
        'Nama Karyawan': item.NamaSecurity || '-',
        'Jumlah Patroli': item.JumlahPatroli,
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [{ wch: 5 }, { wch: 14 }, { wch: 16 }, { wch: 28 }, { wch: 14 }];

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Summary Patroli');
      XLSX.writeFile(wb, `${this.exportFileName('summary')}.xlsx`);
    },

    exportSummaryPDF() {
      const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.text('Review Patroli — Summary', 14, 16);

      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text(this.exportTitle(), 14, 22);

      autoTable(doc, {
        startY: 28,
        head: [['No', 'Tanggal', 'NIK', 'Nama Karyawan', 'Jumlah Patroli']],
        body: this.filteredSummary.map((item, idx) => [
          idx + 1,
          this.formatDate(item.Tanggal),
          item.NIK || '-',
          item.NamaSecurity || '-',
          item.JumlahPatroli,
        ]),
        headStyles: { fillColor: [37, 99, 235], fontSize: 9 },
        bodyStyles: { fontSize: 8.5 },
        columnStyles: {
          0: { cellWidth: 10, halign: 'center' },
          4: { cellWidth: 25, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 250, 252] },
      });

      doc.save(`${this.exportFileName('summary')}.pdf`);
    },

    // ===== Export Detail =====
    exportDetailExcel() {
      const rows = this.filteredDetail.map((item, idx) => ({
        'No': idx + 1,
        'Tanggal & Jam': item.TanggalJam,
        'NIK': item.NIK || '-',
        'Nama Karyawan': item.NamaSecurity || '-',
        'CheckPoint': item.NamaCheckPoint || '-',
        'Koordinat': item.Koordinat || '-',
        'Lokasi': item.NamaLokasi || '-',
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [
        { wch: 5 }, { wch: 22 }, { wch: 16 }, { wch: 28 },
        { wch: 22 }, { wch: 26 }, { wch: 20 },
      ];

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Detail Patroli');
      XLSX.writeFile(wb, `${this.exportFileName('detail')}.xlsx`);
    },

    exportDetailPDF() {
      const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.text('Review Patroli — Detail', 14, 16);

      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text(this.exportTitle(), 14, 22);

      autoTable(doc, {
        startY: 28,
        head: [['No', 'Tanggal & Jam', 'NIK', 'Nama Karyawan', 'CheckPoint', 'Koordinat']],
        body: this.filteredDetail.map((item, idx) => [
          idx + 1,
          item.TanggalJam,
          item.NIK || '-',
          item.NamaSecurity || '-',
          item.NamaCheckPoint || '-',
          item.Koordinat || '-',
        ]),
        headStyles: { fillColor: [37, 99, 235], fontSize: 9 },
        bodyStyles: { fontSize: 8 },
        columnStyles: {
          0: { cellWidth: 10, halign: 'center' },
          1: { cellWidth: 38 },
          2: { cellWidth: 28 },
          5: { cellWidth: 42 },
        },
        alternateRowStyles: { fillColor: [248, 250, 252] },
      });

      doc.save(`${this.exportFileName('detail')}.pdf`);
    },

    onImageError(e) {
      e.target.style.display = 'none';
      e.target.parentElement.innerHTML = '<div style="color:#9ca3af;padding:2rem;text-align:center;font-size:0.875rem;">Foto tidak dapat dimuat</div>';
    },

    // ===== Map =====
    initDetailMap() {
      if (typeof window.L === 'undefined') {
        this.loadLeaflet().then(() => this.buildDetailMap());
      } else {
        this.buildDetailMap();
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

    buildDetailMap() {
      if (!this.detailLatLng) return;
      this.destroyDetailMap();

      const { lat, lng } = this.detailLatLng;
      const L = window.L;
      const mapEl = document.getElementById('detail-map');
      if (!mapEl) return;

      this.detailMap = L.map('detail-map').setView([lat, lng], 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(this.detailMap);

      this.detailMarker = L.marker([lat, lng])
        .addTo(this.detailMap)
        .bindPopup(
          `<strong>${this.selectedDetail.NamaCheckPoint || 'Titik Patroli'}</strong><br>` +
          `${lat.toFixed(6)}, ${lng.toFixed(6)}<br>` +
          `<a href="${this.googleMapsUrl}" target="_blank" rel="noopener">Buka Google Maps</a>`
        )
        .openPopup();
    },

    destroyDetailMap() {
      if (this.detailMap) {
        this.detailMap.remove();
        this.detailMap = null;
        this.detailMarker = null;
      }
    },
  },
};
</script>