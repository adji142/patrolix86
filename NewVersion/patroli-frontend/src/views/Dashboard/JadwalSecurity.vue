<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <button @click="$router.push('/app/master-security')" class="btn btn-secondary" style="padding: 0.3rem 0.75rem;">
          &#8592; Kembali
        </button>
        <h1 class="page-title" style="margin: 0;">
          Jadwal Kerja — <span style="color: #3182ce;">{{ securityName }}</span>
        </h1>
      </div>
    </div>

    <!-- Period Selector -->
    <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
      <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div>
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Bulan</label>
          <select v-model="selectedMonth" @change="fetchJadwal" class="input" style="min-width: 140px;">
            <option v-for="(nama, idx) in bulanList" :key="idx+1" :value="idx+1">{{ nama }}</option>
          </select>
        </div>
        <div>
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Tahun</label>
          <select v-model="selectedYear" @change="fetchJadwal" class="input" style="min-width: 100px;">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <div style="margin-left: auto; color: #718096; font-size: 0.85rem; align-self: center;">
          <span v-if="jadwalData.length > 0">{{ jadwalData.length }} hari terjadwal bulan ini</span>
        </div>
      </div>
    </div>

    <!-- Calendar -->
    <div class="card" style="position: relative; min-height: 300px;">
      <LoadingSpinner v-if="isLoading" />
      <div v-else>
        <!-- Day headers -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 2px;">
          <div
            v-for="h in dayHeaders"
            :key="h"
            style="text-align: center; font-size: 0.8rem; font-weight: 600; padding: 0.5rem; background: #edf2f7; border-radius: 4px; color: #4a5568;"
          >{{ h }}</div>
        </div>

        <!-- Calendar cells -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
          <div
            v-for="(cell, idx) in calendarCells"
            :key="idx"
            :style="cellStyle(cell)"
            @click="cell ? openShiftModal(cell) : null"
          >
            <template v-if="cell">
              <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">{{ cell }}</div>

              <!-- OFF day -->
              <template v-if="jadwalMap[cell] && (jadwalMap[cell].StatusKehadiran === 'OFF' || jadwalMap[cell].shiftid === -1)">
                <div style="font-size: 0.78rem; font-weight: 700; color: #e53e3e; letter-spacing: 0.04em;">OFF</div>
              </template>

              <!-- Scheduled shift -->
              <template v-else-if="jadwalMap[cell] && jadwalMap[cell].shift">
                <div style="font-size: 0.72rem; line-height: 1.3;">
                  <div style="font-weight: 600; color: #2b6cb0;">{{ jadwalMap[cell].shift.NamaShift }}</div>
                  <div style="color: #718096;">
                    {{ formatTime(jadwalMap[cell].shift.MulaiBekerja) }} - {{ formatTime(jadwalMap[cell].shift.SelesaiBekerja) }}
                  </div>
                </div>
              </template>

              <!-- Empty -->
              <template v-else>
                <div style="font-size: 0.72rem; color: #cbd5e0;">Klik untuk atur</div>
              </template>
            </template>
          </div>
        </div>

        <!-- Legend -->
        <div style="margin-top: 1rem; display: flex; gap: 1.25rem; flex-wrap: wrap; font-size: 0.8rem; color: #718096; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
          <div style="display: flex; align-items: center; gap: 0.4rem;">
            <div style="width: 14px; height: 14px; background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 3px;"></div>
            <span>Sudah dijadwalkan</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.4rem;">
            <div style="width: 14px; height: 14px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 3px;"></div>
            <span>OFF</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.4rem;">
            <div style="width: 14px; height: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 3px;"></div>
            <span>Belum dijadwalkan</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Shift Modal -->
    <div
      v-if="showShiftModal"
      style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;"
    >
      <div class="card" style="width: 100%; max-width: 440px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
          <h2 class="page-title" style="margin: 0; font-size: 1.1rem;">
            Jadwal {{ formatTanggal(selectedDate) }}
          </h2>
          <button @click="showShiftModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- OFF checkbox -->
        <div style="margin-bottom: 1rem; padding: 0.65rem 0.85rem; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px;">
          <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; user-select: none;">
            <input
              type="checkbox"
              v-model="isOff"
              style="width: 16px; height: 16px; cursor: pointer; accent-color: #e53e3e;"
            />
            <span style="font-weight: 600; color: #c53030; font-size: 0.95rem;">OFF (Hari Libur / Tidak Bertugas)</span>
          </label>
        </div>

        <!-- Shift dropdown — disabled when OFF -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Pilih Shift</label>
          <select
            v-model="modalShiftId"
            class="input"
            :disabled="isOff"
            :style="isOff ? 'opacity: 0.45; cursor: not-allowed; background: #f7fafc;' : ''"
          >
            <option value="">-- Pilih Shift --</option>
            <option v-for="s in shiftList" :key="s.id" :value="s.id">
              {{ s.NamaShift }} ({{ formatTime(s.MulaiBekerja) }} - {{ formatTime(s.SelesaiBekerja) }})
            </option>
          </select>
        </div>

        <!-- Keterangan — hidden when OFF -->
        <div v-if="!isOff" style="margin-bottom: 1.25rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Keterangan <span style="color:#a0aec0; font-weight:400;">(opsional)</span></label>
          <input v-model="modalKeterangan" class="input" placeholder="Keterangan tambahan..." />
        </div>

        <!-- Info jadwal existing -->
        <div
          v-if="jadwalMap[selectedDate]"
          style="background: #fffbeb; border: 1px solid #f6e05e; border-radius: 6px; padding: 0.6rem 0.85rem; margin-bottom: 1rem; font-size: 0.85rem; color: #744210;"
        >
          <template v-if="jadwalMap[selectedDate].StatusKehadiran === 'OFF' || jadwalMap[selectedDate].shiftid === -1">
            Saat ini: <strong style="color:#c53030;">OFF</strong>
          </template>
          <template v-else>
            Saat ini: <strong>{{ jadwalMap[selectedDate].shift?.NamaShift }}</strong>
            ({{ formatTime(jadwalMap[selectedDate].shift?.MulaiBekerja) }} - {{ formatTime(jadwalMap[selectedDate].shift?.SelesaiBekerja) }})
          </template>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <button
            v-if="jadwalMap[selectedDate]"
            @click="hapusJadwal(selectedDate)"
            class="btn btn-danger"
            :disabled="isSaving"
          >Hapus Jadwal</button>
          <button @click="showShiftModal = false" class="btn btn-secondary">Batal</button>
          <button
            @click="simpanJadwal"
            class="btn btn-primary"
            :disabled="isSaving || (!isOff && !modalShiftId)"
          >{{ isSaving ? 'Menyimpan...' : 'Simpan' }}</button>
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
  name: 'JadwalSecurity',
  components: { LoadingSpinner },

  data() {
    const now = new Date();
    return {
      nik: this.$route.params.nik,
      securityName: this.$route.query.nama || this.$route.params.nik,
      locationID: null,
      selectedMonth: now.getMonth() + 1,
      selectedYear: now.getFullYear(),
      jadwalData: [],
      shiftList: [],
      isLoading: false,
      isSaving: false,
      showShiftModal: false,
      selectedDate: null,
      modalShiftId: '',
      modalKeterangan: '',
      isOff: false,
      bulanList: [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
      ],
      dayHeaders: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    };
  },

  computed: {
    yearOptions() {
      const cur = new Date().getFullYear();
      const years = [];
      for (let y = cur - 1; y <= cur + 2; y++) years.push(y);
      return years;
    },

    calendarCells() {
      const firstDay = new Date(this.selectedYear, this.selectedMonth - 1, 1);
      const daysInMonth = new Date(this.selectedYear, this.selectedMonth, 0).getDate();
      let startDow = firstDay.getDay();
      startDow = (startDow + 6) % 7; // Monday-first

      const cells = [];
      for (let i = 0; i < startDow; i++) cells.push(null);
      for (let d = 1; d <= daysInMonth; d++) cells.push(d);
      while (cells.length % 7 !== 0) cells.push(null);
      return cells;
    },

    jadwalMap() {
      const map = {};
      this.jadwalData.forEach((j) => {
        map[j.Tgl] = j;
      });
      return map;
    },
  },

  mounted() {
    this.init();
  },

  methods: {
    async init() {
      this.isLoading = true;
      try {
        const res = await axios.get(`/master-security/${encodeURIComponent(this.nik)}`);
        this.locationID = res.data.data.LocationID || null;
        if (res.data.data.NamaSecurity) {
          this.securityName = res.data.data.NamaSecurity;
        }
      } catch (e) {
        console.error('Gagal load data security:', e);
      } finally {
        this.isLoading = false;
      }
      await Promise.all([this.fetchShifts(), this.fetchJadwal()]);
    },

    async fetchShifts() {
      try {
        const params = {};
        if (this.locationID) params.LocationID = this.locationID;
        const res = await axios.get('/tshift', { params });
        this.shiftList = res.data.data;
      } catch (e) {
        console.error('Gagal load shift:', e);
      }
    },

    async fetchJadwal() {
      this.isLoading = true;
      try {
        const res = await axios.get('/jadwal-kerja', {
          params: { nik: this.nik, bulan: this.selectedMonth, tahun: this.selectedYear },
        });
        this.jadwalData = res.data.data;
      } catch (e) {
        console.error('Gagal load jadwal:', e);
      } finally {
        this.isLoading = false;
      }
    },

    openShiftModal(day) {
      if (!day) return;

      const dateStr = `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const targetDate = new Date(dateStr);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (targetDate < today) {
        Swal.fire({
          icon: 'info',
          title: 'Informasi',
          text: 'Jadwal yang sudah terlewati tidak dapat diubah.',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }

      const existing = this.jadwalMap[day];
      this.selectedDate = day;
      this.isOff = existing?.StatusKehadiran === 'OFF' || existing?.shiftid === -1;
      this.modalShiftId = existing?.shiftid || '';
      this.modalKeterangan = existing?.KeteranganStatusKehadiran || '';
      this.showShiftModal = true;
    },

    async simpanJadwal() {
      if (!this.isOff && !this.modalShiftId) return;
      this.isSaving = true;

      const tanggal = `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}-${String(this.selectedDate).padStart(2, '0')}`;

      try {
        await axios.post('/jadwal-kerja', {
          KodeKaryawan: this.nik,
          Tanggal: tanggal,
          shiftid: this.isOff ? -1 : this.modalShiftId,
          StatusKehadiran: this.isOff ? 'OFF' : null,
          KeteranganStatusKehadiran: this.isOff ? null : (this.modalKeterangan || null),
        });
        await this.fetchJadwal();
        this.showShiftModal = false;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Jadwal disimpan',
          showConfirmButton: false,
          timer: 1800,
        });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal menyimpan jadwal.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async hapusJadwal(day) {
      const jadwal = this.jadwalMap[day];
      if (!jadwal) return;

      const result = await Swal.fire({
        title: 'Hapus jadwal tanggal ' + this.formatTanggal(day) + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e53e3e',
      });
      if (!result.isConfirmed) return;

      this.isSaving = true;
      try {
        await axios.delete(`/jadwal-kerja/${jadwal.id}`);
        await this.fetchJadwal();
        this.showShiftModal = false;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Jadwal dihapus',
          showConfirmButton: false,
          timer: 1800,
        });
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Gagal menghapus jadwal.', 'error');
      } finally {
        this.isSaving = false;
      }
    },

    formatTanggal(day) {
      if (!day) return '';
      return `${String(day).padStart(2, '0')} ${this.bulanList[this.selectedMonth - 1]} ${this.selectedYear}`;
    },

    formatTime(val) {
      if (!val) return '';
      return String(val).slice(0, 5);
    },

    cellStyle(day) {
      const base = {
        minHeight: '80px',
        borderRadius: '6px',
        padding: '0.4rem 0.5rem',
        cursor: day ? 'pointer' : 'default',
        border: '1px solid #e2e8f0',
        transition: 'background 0.15s',
      };
      if (!day) {
        return { ...base, background: '#f7fafc', cursor: 'default', opacity: 0.4 };
      }

      const dateStr = `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const targetDate = new Date(dateStr);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const isPast = targetDate < today;

      if (isPast) {
          base.opacity = 0.7;
          base.background = '#f1f5f9';
      }
      const jadwal = this.jadwalMap[day];
      if (jadwal) {
        if (jadwal.StatusKehadiran === 'OFF' || jadwal.shiftid === -1) {
          return { ...base, background: '#fff5f5', border: '1px solid #fed7d7' };
        }
        return { ...base, background: '#ebf8ff', border: '1px solid #bee3f8' };
      }
      return { ...base, background: '#ffffff' };
    },
  },
};
</script>