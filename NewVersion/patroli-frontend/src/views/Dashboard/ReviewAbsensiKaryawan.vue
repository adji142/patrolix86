<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <div style="display: flex; align-items: center; gap: 1rem;">
        <button @click="$router.back()" class="btn btn-secondary" style="white-space: nowrap;">&#8592; Kembali</button>
        <h1 class="page-title">Realisasi Absensi</h1>
      </div>
    </div>

    <!-- Info Karyawan -->
    <div class="card" style="margin-bottom: 1.25rem;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else-if="karyawan" style="display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <div style="width: 42px; height: 42px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">&#128100;</div>
          <div>
            <div style="font-weight: 700; font-size: 1rem; color: #111827;">{{ karyawan.NamaSecurity || '-' }}</div>
            <div style="font-size: 0.8rem; color: #6b7280; font-family: monospace;">{{ karyawan.NIK }}</div>
          </div>
        </div>
        <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-left: auto; align-items: center;">
          <div style="text-align: center;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Lokasi</div>
            <div style="font-weight: 600; font-size: 0.875rem; color: #374151;">{{ karyawan.NamaLokasi || '-' }}</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Periode</div>
            <div style="font-weight: 600; font-size: 0.875rem; color: #374151;">{{ formatDate(query.tgl_awal) }} &mdash; {{ formatDate(query.tgl_akhir) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Legenda -->
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem; align-items: center;">
      <span style="font-size: 0.8rem; color: #6b7280; font-weight: 500;">Legenda:</span>
      <span v-for="s in statusLegend" :key="s.key" :style="s.badgeStyle" style="font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.75rem; border-radius: 9999px;">{{ s.label }}</span>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="card" style="text-align: center; padding: 3rem; color: #9ca3af;">
      <LoadingSpinner />
    </div>

    <!-- Kalender per Bulan -->
    <div v-else style="display: flex; flex-direction: column; gap: 1.5rem;">
      <div v-for="month in computedMonths" :key="month.key" class="card" style="padding: 1rem;">
        <!-- Header Bulan -->
        <div style="text-align: center; font-weight: 700; font-size: 1rem; color: #1e3a8a; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eff6ff;">
          {{ month.label }}
        </div>

        <!-- Header Hari -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 4px;">
          <div v-for="day in ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu']" :key="day"
            style="text-align: center; font-size: 0.75rem; font-weight: 600; color: #6b7280; padding: 6px 0; background: #f9fafb; border-radius: 4px;">
            {{ day }}
          </div>
        </div>

        <!-- Grid Hari -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
          <template v-for="(cell, ci) in month.cells" :key="ci">
            <!-- Padding -->
            <div v-if="cell === null" style="min-height: 110px;"></div>

            <!-- Di luar range -->
            <div v-else-if="!cell.inRange"
              style="min-height: 110px; background: #f9fafb; border-radius: 6px; padding: 8px; opacity: 0.35;">
              <div style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">{{ cell.d }}</div>
            </div>

            <!-- Hari dalam range -->
            <div v-else
              :style="cellBgStyle(cell)"
              style="min-height: 110px; border-radius: 6px; padding: 8px; cursor: pointer; position: relative;"
              @click="openModal(cell)"
            >
              <!-- Nomor tanggal + status pill -->
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #374151;">{{ cell.d }}</span>
                <span :style="statusPillStyle(cell)" style="font-size: 0.6rem; font-weight: 700; padding: 2px 6px; border-radius: 9999px; white-space: nowrap;">
                  {{ cellStatus(cell) }}
                </span>
              </div>

              <!-- Nama Shift -->
              <div v-if="cell.jadwal && cell.jadwal.NamaShift" style="font-size: 0.7rem; color: #4b5563; line-height: 1.3; margin-bottom: 2px; font-weight: 500;">
                {{ cell.jadwal.NamaShift }}
              </div>

              <!-- Jam kerja jadwal (rencana) -->
              <div v-if="cell.jadwal && cell.jadwal.MulaiBekerja" style="font-size: 0.68rem; color: #6b7280;">
                {{ cell.jadwal.MulaiBekerja.slice(0,5) }} &ndash; {{ cell.jadwal.SelesaiBekerja.slice(0,5) }}
              </div>

              <!-- Jam Checkin/Checkout realisasi -->
              <template v-if="cellStatus(cell) === 'MASUK' && cell.absensi">
                <div style="font-size: 0.7rem; color: #15803d; font-weight: 600; margin-top: 4px;">
                  &#8599; {{ formatTime(cell.absensi.Checkin) }}
                  <span v-if="hasCheckout(cell.absensi.CheckOut)"> &nbsp;&#8601; {{ formatTime(cell.absensi.CheckOut) }}</span>
                </div>
              </template>

              <!-- Keterangan IZIN/CUTI dari jadwal -->
              <div v-if="cell.jadwal && cell.jadwal.KeteranganStatusKehadiran"
                style="font-size: 0.65rem; color: #78350f; margin-top: 3px; word-break: break-word;">
                {{ cell.jadwal.KeteranganStatusKehadiran }}
              </div>

              <!-- Keterangan dari absensi -->
              <div v-if="cell.absensi && cell.absensi.Keterangan"
                style="font-size: 0.65rem; color: #1d4ed8; margin-top: 3px; word-break: break-word; font-style: italic;">
                {{ cell.absensi.Keterangan }}
              </div>
            </div>
          </template>
        </div>

        <!-- Rekap bulan ini -->
        <div style="margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid #f3f4f6; display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center;">
          <span v-for="s in monthStats(month)" :key="s.key" :style="s.style" style="font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.75rem; border-radius: 9999px;">
            {{ s.label }}: {{ s.count }}
          </span>
        </div>
      </div>
    </div>

    <!-- Modal Detail Absensi -->
    <div v-if="showModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="closeModal">
      <div class="card" style="width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; position: relative;">
        <!-- Header Modal -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
          <div>
            <div style="font-weight: 700; font-size: 1rem; color: #111827;">
              {{ selectedCell ? formatDateLong(selectedCell.date) : '' }}
            </div>
            <div v-if="selectedCell" style="margin-top: 4px;">
              <span :style="statusPillStyle(selectedCell)" style="font-size: 0.75rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px;">
                {{ cellStatus(selectedCell) }}
              </span>
            </div>
          </div>
          <button @click="closeModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
        </div>

        <template v-if="selectedCell">
          <!-- Info Jadwal -->
          <div v-if="selectedCell.jadwal" style="background: #f9fafb; border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem;">
            <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Jadwal</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: #374151;">{{ selectedCell.jadwal.NamaShift || '-' }}</div>
            <div v-if="selectedCell.jadwal.MulaiBekerja" style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">
              {{ selectedCell.jadwal.MulaiBekerja.slice(0,5) }} &ndash; {{ selectedCell.jadwal.SelesaiBekerja.slice(0,5) }}
            </div>
            <div v-if="selectedCell.jadwal.KeteranganStatusKehadiran" style="font-size: 0.8rem; color: #92400e; margin-top: 4px;">
              {{ selectedCell.jadwal.KeteranganStatusKehadiran }}
            </div>
          </div>
          <div v-else style="background: #fff7ed; border-radius: 6px; padding: 0.6rem 0.75rem; margin-bottom: 1rem; font-size: 0.8rem; color: #92400e;">
            Tidak ada jadwal kerja untuk tanggal ini.
          </div>

          <!-- Info Absensi -->
          <template v-if="selectedCell.absensi">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
              <div style="background: #f0fdf4; border-radius: 6px; padding: 0.6rem 0.75rem;">
                <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Check In</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #15803d; margin-top: 2px;">{{ formatTime(selectedCell.absensi.Checkin) }}</div>
              </div>
              <div style="background: #f0fdf4; border-radius: 6px; padding: 0.6rem 0.75rem;">
                <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Check Out</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #374151; margin-top: 2px;">
                  {{ hasCheckout(selectedCell.absensi.CheckOut) ? formatTime(selectedCell.absensi.CheckOut) : '-' }}
                </div>
              </div>
            </div>

            <!-- Foto -->
            <div v-if="selectedCell.absensi.ImageIN" style="margin-bottom: 1rem;">
              <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Foto Check In</div>
              <img :src="imageUrl(selectedCell.absensi.ImageIN)"
                style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;"
                @error="(e) => e.target.style.display = 'none'" />
            </div>

            <!-- Koordinat -->
            <div v-if="selectedCell.absensi.KoordinatIN" style="margin-bottom: 1rem;">
              <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Koordinat Check In</div>
              <div style="font-size: 0.8rem; color: #374151; font-family: monospace; background: #f9fafb; padding: 0.5rem 0.75rem; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>{{ selectedCell.absensi.KoordinatIN }}</span>
                <a :href="googleMapsUrl(selectedCell.absensi.KoordinatIN)" target="_blank"
                  style="font-size: 0.75rem; color: #1d4ed8; text-decoration: none; font-family: sans-serif; font-weight: 600; margin-left: 0.5rem; white-space: nowrap;">
                  Lihat Peta &#8599;
                </a>
              </div>
            </div>

            <!-- Keterangan -->
            <div>
              <div style="font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Keterangan</div>
              <textarea
                v-model="modalKeterangan"
                class="input"
                rows="3"
                placeholder="Tambahkan keterangan..."
                style="width: 100%; resize: vertical; font-size: 0.875rem;"
              ></textarea>
              <div style="display: flex; justify-content: flex-end; margin-top: 0.6rem;">
                <button @click="saveKeterangan" class="btn btn-primary" :disabled="isSaving" style="min-width: 100px;">
                  {{ isSaving ? 'Menyimpan...' : 'Simpan Keterangan' }}
                </button>
              </div>
            </div>
          </template>

          <!-- Tidak ada absensi -->
          <div v-else style="text-align: center; padding: 2rem 0; color: #9ca3af;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">&#128683;</div>
            <div style="font-size: 0.875rem;">Tidak ada data absensi untuk tanggal ini.</div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios.js';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import Swal from 'sweetalert2';

export default {
  name: 'ReviewAbsensiKaryawan',
  components: { LoadingSpinner },

  data() {
    return {
      isLoading: false,
      karyawan: null,
      jadwalMap: {},
      absensiMap: {},

      query: {
        nik: this.$route.query.nik || '',
        tgl_awal: this.$route.query.tgl_awal || '',
        tgl_akhir: this.$route.query.tgl_akhir || '',
        location_id: this.$route.query.location_id || '',
      },

      showModal: false,
      selectedCell: null,
      modalKeterangan: '',
      isSaving: false,

      statusLegend: [
        { key: 'MASUK', label: 'MASUK',  badgeStyle: 'background:#dcfce7;color:#16a34a;' },
        { key: 'OFF',   label: 'OFF',    badgeStyle: 'background:#f3f4f6;color:#6b7280;' },
        { key: 'IZIN',  label: 'IZIN',   badgeStyle: 'background:#fef3c7;color:#d97706;' },
        { key: 'CUTI',  label: 'CUTI',   badgeStyle: 'background:#dbeafe;color:#1d4ed8;' },
      ],
    };
  },

  computed: {
    computedMonths() {
      if (!this.query.tgl_awal || !this.query.tgl_akhir) return [];

      const start = new Date(this.query.tgl_awal + 'T00:00:00');
      const end   = new Date(this.query.tgl_akhir + 'T00:00:00');
      const months = [];

      let cur = new Date(start.getFullYear(), start.getMonth(), 1);
      const endMonth = new Date(end.getFullYear(), end.getMonth(), 1);

      while (cur <= endMonth) {
        const y = cur.getFullYear();
        const m = cur.getMonth();

        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const cells = [];

        // Monday-based week padding
        const firstDow = (new Date(y, m, 1).getDay() + 6) % 7;
        for (let i = 0; i < firstDow; i++) cells.push(null);

        for (let d = 1; d <= daysInMonth; d++) {
          const mm  = String(m + 1).padStart(2, '0');
          const dd  = String(d).padStart(2, '0');
          const dateStr = `${y}-${mm}-${dd}`;
          const dateObj = new Date(y, m, d);
          const inRange = dateObj >= start && dateObj <= end;

          cells.push({
            date: dateStr,
            d,
            inRange,
            jadwal: this.jadwalMap[dateStr] || null,
            absensi: this.absensiMap[dateStr] || null,
          });
        }

        while (cells.length % 7 !== 0) cells.push(null);

        months.push({
          key: `${y}-${m + 1}`,
          label: new Date(y, m).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }),
          cells,
        });

        cur = new Date(y, m + 1, 1);
      }

      return months;
    },
  },

  mounted() {
    if (!this.query.nik || !this.query.tgl_awal || !this.query.tgl_akhir) {
      Swal.fire('Error', 'Parameter tidak lengkap.', 'error').then(() => this.$router.back());
      return;
    }
    this.fetchData();
  },

  methods: {
    async fetchData() {
      this.isLoading = true;
      try {
        const params = {
          nik:      this.query.nik,
          tgl_awal: this.query.tgl_awal,
          tgl_akhir: this.query.tgl_akhir,
        };
        if (this.query.location_id) params.location_id = this.query.location_id;

        const res = await axios.get('/review-absensi/karyawan', { params });
        this.karyawan   = res.data.karyawan;
        this.jadwalMap  = res.data.jadwal  || {};
        this.absensiMap = res.data.absensi || {};
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal memuat data.', 'error');
      } finally {
        this.isLoading = false;
      }
    },

    openModal(cell) {
      this.selectedCell = cell;
      this.modalKeterangan = cell.absensi?.Keterangan || '';
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.selectedCell = null;
      this.modalKeterangan = '';
    },

    async saveKeterangan() {
      if (!this.selectedCell?.absensi?.id) return;

      this.isSaving = true;
      try {
        await axios.patch(`/review-absensi/record/${this.selectedCell.absensi.id}/keterangan`, {
          Keterangan: this.modalKeterangan,
        });

        // Update local map so the cell reflects the saved value
        const date = this.selectedCell.date;
        if (this.absensiMap[date]) {
          this.absensiMap[date] = { ...this.absensiMap[date], Keterangan: this.modalKeterangan };
          this.selectedCell = { ...this.selectedCell, absensi: this.absensiMap[date] };
        }

        Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'Keterangan berhasil disimpan.', timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal menyimpan keterangan.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    cellStatus(cell) {
      const jk = cell.jadwal;
      const ab = cell.absensi;

      if (jk?.StatusKehadiran === 'IZIN') return 'IZIN';
      if (jk?.StatusKehadiran === 'CUTI') return 'CUTI';
      if (jk?.StatusKehadiran === 'OFF')  return 'OFF';
      if (ab?.Checkin && ab.Checkin !== '') return 'MASUK';
      return 'OFF';
    },

    cellBgStyle(cell) {
      const st = this.cellStatus(cell);
      const map = {
        MASUK: 'background:#f0fdf4; border-top: 3px solid #16a34a;',
        OFF:   'background:#f9fafb; border-top: 3px solid #d1d5db;',
        IZIN:  'background:#fefce8; border-top: 3px solid #eab308;',
        CUTI:  'background:#eff6ff; border-top: 3px solid #3b82f6;',
      };
      return map[st] || map.OFF;
    },

    statusPillStyle(cell) {
      const st = this.cellStatus(cell);
      const map = {
        MASUK: 'background:#dcfce7;color:#16a34a;',
        OFF:   'background:#f3f4f6;color:#6b7280;',
        IZIN:  'background:#fef3c7;color:#d97706;',
        CUTI:  'background:#dbeafe;color:#1d4ed8;',
      };
      return map[st] || map.OFF;
    },

    hasCheckout(val) {
      return val && val !== '00:00:00' && val !== '';
    },

    monthStats(month) {
      const counts = { MASUK: 0, OFF: 0, IZIN: 0, CUTI: 0 };
      month.cells.forEach((cell) => {
        if (cell && cell.inRange) counts[this.cellStatus(cell)]++;
      });
      return [
        { key: 'MASUK', label: 'Masuk', count: counts.MASUK, style: 'background:#dcfce7;color:#16a34a;' },
        { key: 'OFF',   label: 'OFF',   count: counts.OFF,   style: 'background:#f3f4f6;color:#6b7280;' },
        { key: 'IZIN',  label: 'Izin',  count: counts.IZIN,  style: 'background:#fef3c7;color:#d97706;' },
        { key: 'CUTI',  label: 'Cuti',  count: counts.CUTI,  style: 'background:#dbeafe;color:#1d4ed8;' },
      ].filter(s => s.count > 0);
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      const [y, m, d] = dateStr.split('-');
      return `${d}/${m}/${y}`;
    },

    formatDateLong(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    },

    formatTime(val) {
      if (!val || val === '00:00:00') return '-';
      const str = String(val);
      return str.includes(' ') ? str.split(' ')[1].slice(0, 5) : str.slice(0, 5);
    },

    imageUrl(filename) {
      const base = import.meta.env.VITE_LEGACY_ASSET_URL || 'http://localhost/patrolix86';
      return `${base}/Assets/images/Absensi/${filename}`;
    },

    googleMapsUrl(koordinat) {
      if (!koordinat) return '#';
      const parts = koordinat.split(',');
      if (parts.length < 2) return '#';
      return `https://www.google.com/maps?q=${parts[0].trim()},${parts[1].trim()}`;
    },
  },
};
</script>