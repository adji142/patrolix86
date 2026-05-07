<template>
  <div>
    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="page-title">Master User</h1>
      <button @click="openCreateModal" class="btn btn-primary">+ Tambah User</button>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
      <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="min-width: 160px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Role</label>
          <select v-model="filterRoleId" class="input">
            <option value="">Semua Role</option>
            <option v-for="r in roleList" :key="r.id" :value="r.id">{{ r.rolename }}</option>
          </select>
        </div>
        <div>
          <button @click="fetchItems" class="btn btn-secondary">Filter</button>
        </div>
        <div style="flex: 1; min-width: 200px;">
          <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">Cari Username / Nama</label>
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
                <th>No</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Lokasi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="paginatedItems.length === 0">
                <td colspan="6" style="text-align: center; color: #888; padding: 2rem;">
                  Tidak ada data user.
                </td>
              </tr>
              <tr v-for="(item, idx) in paginatedItems" :key="item.id">
                <td>{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                <td style="font-weight: 500;">{{ item.username }}</td>
                <td>{{ item.nama }}</td>
                <td>
                  <span v-if="item.rolename" class="badge" style="background:#e9d8fd;color:#553c9a;">
                    {{ item.rolename }}
                  </span>
                  <span v-else style="color:#a0aec0;">-</span>
                </td>
                <td>
                  <span
                    v-for="ul in item.user_lokasi"
                    :key="ul.id"
                    class="badge"
                    style="background:#c6f6d5;color:#276749; margin-right: 0.25rem; margin-bottom: 0.25rem;"
                  >{{ ul.lokasi?.NamaArea }}</span>
                  <span v-if="!item.user_lokasi || item.user_lokasi.length === 0" style="color:#a0aec0;">-</span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.5rem;">
                    <button @click="editItem(item.id)" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Edit</button>
                    <button @click="deleteItem(item.id, item.username)" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Hapus</button>
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
      <div class="card" style="width: 100%; max-width: 560px; margin: auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 class="page-title" style="margin: 0;">{{ isEditing ? 'Edit User' : 'Tambah User' }}</h2>
          <button @click="showModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Username -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Username <span style="color:#e53e3e;">*</span>
          </label>
          <input
            v-model="form.username"
            class="input"
            placeholder="Username untuk login"
            :readonly="isEditing"
            :style="isEditing ? 'background:#f7fafc; cursor:not-allowed; color:#718096;' : ''"
          />
          <p v-if="isEditing" style="font-size: 0.75rem; color: #718096; margin: 0.25rem 0 0;">Username tidak dapat diubah setelah disimpan.</p>
        </div>

        <!-- Nama -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Nama User <span style="color:#e53e3e;">*</span>
          </label>
          <input v-model="form.nama" class="input" placeholder="Nama lengkap user" />
        </div>

        <!-- Password -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Password <span v-if="!isEditing" style="color:#e53e3e;">*</span>
          </label>
          <div style="display: flex; gap: 0.5rem;">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              class="input"
              :placeholder="isEditing ? 'Kosongkan jika tidak ingin mengubah' : 'Password'"
              style="flex: 1;"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="btn btn-secondary"
              style="padding: 0 0.85rem; white-space: nowrap;"
            >{{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}</button>
          </div>
          <p v-if="isEditing" style="font-size: 0.75rem; color: #718096; margin: 0.25rem 0 0;">Kosongkan jika tidak ingin mengubah password.</p>
        </div>

        <!-- Role -->
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">
            Role <span style="color:#e53e3e;">*</span>
          </label>
          <select v-model="form.role_id" class="input">
            <option value="">Pilih Role...</option>
            <option v-for="r in roleList" :key="r.id" :value="r.id">{{ r.rolename }}</option>
          </select>
        </div>

        <!-- Lokasi (multiple checkbox) -->
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
            Lokasi <span style="color:#e53e3e;">*</span>
            <span v-if="!isSecurityRole" style="font-weight: 400; color: #718096; font-size: 0.8rem;">(pilih satu atau lebih)</span>
          </label>

          <!-- Notifikasi jika role Security -->
          <div
            v-if="isSecurityRole"
            style="background: #fffbeb; border: 1px solid #f6e05e; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.85rem; color: #744210;"
          >
            Lokasi untuk user dengan role <strong>Security</strong> dikelola melalui halaman <strong>Master Security</strong>.
          </div>

          <div
            v-else
            style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem; max-height: 180px; overflow-y: auto; background: #fafafa;"
          >
            <div v-if="lokasiList.length === 0" style="color: #a0aec0; font-size: 0.85rem; padding: 0.5rem 0;">
              Tidak ada lokasi tersedia.
            </div>
            <label
              v-for="loc in lokasiList"
              :key="loc.id"
              style="display: flex; align-items: center; gap: 0.6rem; padding: 0.35rem 0; cursor: pointer; user-select: none;"
            >
              <input
                type="checkbox"
                :value="loc.id"
                v-model="form.location_ids"
                style="width: 15px; height: 15px; cursor: pointer;"
              />
              <span style="font-size: 0.9rem;">{{ loc.NamaArea }}</span>
            </label>
          </div>
          <p v-if="!isSecurityRole && form.location_ids.length === 0" style="font-size: 0.75rem; color: #e53e3e; margin: 0.25rem 0 0;">Pilih minimal satu lokasi.</p>
        </div>

        <!-- Footer -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
          <button @click="showModal = false" class="btn btn-secondary">Batal</button>
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
  name: 'MasterUser',
  components: { LoadingSpinner },

  data() {
    return {
      items: [],
      roleList: [],
      lokasiList: [],
      isLoading: false,
      isSaving: false,
      showModal: false,
      isEditing: false,
      showPassword: false,
      searchQuery: '',
      filterRoleId: '',
      currentPage: 1,
      itemsPerPage: 10,
      form: this.defaultForm(),
    };
  },

  computed: {
    isSecurityRole() {
      if (!this.form.role_id) return false;
      const role = this.roleList.find((r) => r.id == this.form.role_id);
      return role?.rolename?.toLowerCase() === 'security';
    },
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();
      return this.items.filter(
        (i) =>
          i.username?.toLowerCase().includes(q) ||
          i.nama?.toLowerCase().includes(q),
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
    this.fetchRoles();
    this.fetchLokasi();
    this.fetchItems();
  },

  methods: {
    defaultForm() {
      return {
        username: '',
        nama: '',
        password: '',
        role_id: '',
        location_ids: [],
      };
    },

    async fetchRoles() {
      try {
        const res = await axios.get('/roles');
        this.roleList = res.data.data;
      } catch (e) {
        console.error('Gagal load roles:', e);
      }
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
        if (this.filterRoleId) params.role_id = this.filterRoleId;
        const res = await axios.get('/master-user', { params });
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
      this.showPassword = false;
      this.showModal = true;
    },

    async editItem(id) {
      Swal.fire({
        title: 'Memuat data...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });
      try {
        const res = await axios.get(`/master-user/${id}`);
        const d = res.data.data;
        this.form = {
          id: d.id,
          username: d.username,
          nama: d.nama,
          password: '',
          role_id: d.role_id || '',
          location_ids: d.user_lokasi?.map((ul) => ul.location_id) || [],
        };
        this.isEditing = true;
        this.showPassword = false;
        this.showModal = true;
        Swal.close();
      } catch (e) {
        Swal.fire('Error', 'Gagal memuat data user.', 'error');
      }
    },

    async saveItem() {
      if (!this.form.username.trim()) {
        return Swal.fire('Perhatian', 'Username tidak boleh kosong.', 'warning');
      }
      if (!this.form.nama.trim()) {
        return Swal.fire('Perhatian', 'Nama tidak boleh kosong.', 'warning');
      }
      if (!this.isEditing && !this.form.password.trim()) {
        return Swal.fire('Perhatian', 'Password tidak boleh kosong.', 'warning');
      }
      if (!this.form.role_id) {
        return Swal.fire('Perhatian', 'Role harus dipilih.', 'warning');
      }
      if (!this.isSecurityRole && this.form.location_ids.length === 0) {
        return Swal.fire('Perhatian', 'Pilih minimal satu lokasi.', 'warning');
      }

      this.isSaving = true;
      try {
        const payload = {
          username: this.form.username,
          nama: this.form.nama,
          role_id: this.form.role_id,
          location_ids: this.form.location_ids,
        };
        if (this.form.password) payload.password = this.form.password;

        if (this.isEditing) {
          await axios.put(`/master-user/${this.form.id}`, payload);
        } else {
          await axios.post('/master-user', payload);
        }
        this.showModal = false;
        await this.fetchItems();
        Swal.fire('Berhasil!', 'Data user berhasil disimpan.', 'success');
      } catch (e) {
        const errors = e.response?.data?.errors;
        const msg = errors
          ? Object.values(errors).flat().join('\n')
          : e.response?.data?.message || 'Terjadi kesalahan.';
        Swal.fire('Error', msg, 'error');
      } finally {
        this.isSaving = false;
      }
    },

    async deleteItem(id, username) {
      const result = await Swal.fire({
        title: 'Yakin hapus user ini?',
        html: `Username: <strong>${username}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e53e3e',
      });
      if (!result.isConfirmed) return;

      Swal.fire({
        title: 'Menghapus...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });
      try {
        await axios.delete(`/master-user/${id}`);
        await this.fetchItems();
        Swal.fire('Dihapus!', 'User berhasil dihapus.', 'success');
      } catch (e) {
        Swal.fire('Gagal', e.response?.data?.message || 'Gagal menghapus user.', 'error');
      }
    },
  },
};
</script>