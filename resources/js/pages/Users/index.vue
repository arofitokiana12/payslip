<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('users.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('users.new') }}
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Filtres -->
        <div class="row mb-3">
          <div class="col-md-3">
            <input
              type="text"
              class="form-control"
              :placeholder="$t('users.search_placeholder')"
              v-model="filters.search"
              @input="applyFilters"
            />
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="filters.role_id" @change="applyFilters">
              <option value="">{{ $t('common.all_roles') }}</option>
              <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                {{ role.name }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="filters.active" @change="applyFilters">
              <option value="">{{ $t('common.all_statuses') }}</option>
              <option value="true">{{ $t('common.active_plural') }}</option>
              <option value="false">{{ $t('common.inactive_plural') }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-secondary w-100" @click="resetFilters">
              <i class="fas fa-redo"></i> {{ $t('common.reset') }}
            </button>
          </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status"></div>
        </div>

        <!-- Tableau -->
        <div v-else class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>{{ $t('common.matricule') }}</th>
                <th>{{ $t('common.name') }}</th>
                <th>{{ $t('users.email') }}</th>
                <th>{{ $t('users.username') }}</th>
                <th>{{ $t('users.role') }}</th>
                <th>{{ $t('users.company') }}</th>
                <th>{{ $t('common.status') }}</th>
                <th>{{ $t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="users.length === 0">
                <td colspan="8" class="text-center text-muted">
                  {{ $t('users.no_found') }}
                </td>
              </tr>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.user_name }}</td>
                <td>
                  <span class="badge bg-info">
                    {{ user.role?.name || '-' }}
                  </span>
                </td>
                <td>{{ user.company?.company_name || '-' }}</td>
                <td>
                  <span :class="user.active ? 'badge bg-success' : 'badge bg-danger'">
                    {{ user.active ? $t('common.active') : $t('common.inactive') }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-info"
                    @click="viewUser(user.id)"
                    :title="$t('common.view_details')"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning ms-1"
                    @click="editUser(user)"
                    :title="$t('common.edit')"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-primary ms-1"
                    @click="openChangePasswordModal(user)"
                    :title="$t('users.change_password')"
                  >
                    <i class="fas fa-key"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteUser(user.id)"
                    :title="$t('common.delete')"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? $t('users.edit') : $t('users.new') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.full_name') }} *</label>
                  <input type="text" class="form-control" v-model="form.name" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.username') }} *</label>
                  <input type="text" class="form-control" v-model="form.user_name" required />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.email') }} *</label>
                  <input type="email" class="form-control" v-model="form.email" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.role') }} *</label>
                  <select class="form-select" v-model="form.role_id" required>
                    <option value="">-- {{ $t('common.select') }} --</option>
                    <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                      {{ role.name }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="row" v-if="!isEditing">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.password') }} *</label>
                  <input type="password" class="form-control" v-model="form.password" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.confirm_password') }} *</label>
                  <input type="password" class="form-control" v-model="form.password_confirmation" required />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('users.company') }} *</label>
                  <select class="form-select" v-model="form.company_id" required>
                    <option value="">-- {{ $t('common.select') }} --</option>
                    <option v-for="company in companies" :key="company.company_id" :value="company.company_id">
                      {{ company.company_name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('common.status') }}</label>
                  <select class="form-select" v-model="form.active">
                    <option :value="true">{{ $t('common.active') }}</option>
                    <option :value="false">{{ $t('common.inactive') }}</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('common.cancel') }}</button>
            <button type="button" class="btn btn-primary" @click="saveUser">{{ $t('common.save') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Change Password -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('users.change_password') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">{{ $t('users.new_password') }} *</label>
                <input type="password" class="form-control" v-model="passwordForm.password" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('users.confirm_password') }} *</label>
                <input type="password" class="form-control" v-model="passwordForm.password_confirmation" required />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" @click="changePassword">{{ $t('users.change') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="userDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('users.details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedUser">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th width="150">{{ $t('common.matricule') }}</th>
                  <td>{{ selectedUser.id }}</td>
                </tr>
                <tr>
                  <th>{{ $t('common.name') }}</th>
                  <td>{{ selectedUser.name }}</td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td>{{ selectedUser.email }}</td>
                </tr>
                <tr>
                  <th>Username</th>
                  <td>{{ selectedUser.user_name }}</td>
                </tr>
                <tr>
                  <th>Rôle</th>
                  <td><span class="badge bg-info">{{ selectedUser.role?.name }}</span></td>
                </tr>
                <tr>
                  <th>Entreprise</th>
                  <td>{{ selectedUser.company?.company_name }}</td>
                </tr>
                <tr>
                  <th>Statut</th>
                  <td>
                    <span :class="selectedUser.active ? 'badge bg-success' : 'badge bg-danger'">
                      {{ selectedUser.active ? 'Actif' : 'Inactif' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios'

export default {
  name: 'Users',

  data() {
    return {
      users: [],
      roles: [],
      companies: [],
      loading: false,
      isEditing: false,
      form: this.getEmptyForm(),
      passwordForm: {
        id: null,
        password: '',
        password_confirmation: ''
      },
      filters: {
        search: '',
        role_id: '',
        company_id: '',
        active: ''
      },
      modal: null,
      passwordModal: null,
      detailModal: null,
      selectedUser: null
    };
  },

  mounted() {
    this.fetchUsers();
    this.fetchRoles();
    this.fetchCompanies();
    this.$nextTick(() => {
      const userModalEl = document.getElementById('userModal');
      const passwordModalEl = document.getElementById('passwordModal');
      const detailModalEl = document.getElementById('userDetailModal');
      if (userModalEl) this.modal = new Modal(userModalEl);
      if (passwordModalEl) this.passwordModal = new Modal(passwordModalEl);
      if (detailModalEl) this.detailModal = new Modal(detailModalEl);
    });
  },

  methods: {
    getEmptyForm() {
      return {
        id: null,
        name: '',
        email: '',
        user_name: '',
        password: '',
        password_confirmation: '',
        role_id: '',
        company_id: '',
        active: true
      };
    },

    async fetchUsers() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        if (this.filters.search) params.append('search', this.filters.search);
        if (this.filters.role_id) params.append('role_id', this.filters.role_id);
        if (this.filters.company_id) params.append('company_id', this.filters.company_id);
        if (this.filters.active !== '') params.append('active', this.filters.active);

        const response = await axios.get(`/users?${params.toString()}`);
        const data = response.data?.data ?? response.data;
        this.users = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des utilisateurs');
      } finally {
        this.loading = false;
      }
    },

    async fetchRoles() {
      try {
        const response = await axios.get('/roles');
        const data = response.data?.data ?? response.data;
        this.roles = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchCompanies() {
      try {
        const response = await axios.get('/companies');
        const data = response.data?.data ?? response.data;
        this.companies = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async viewUser(id) {
      try {
        const response = await axios.get(`/users/${id}`);
        this.selectedUser = response.data?.data ?? response.data;
        this.detailModal?.show();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de l\'utilisateur');
      }
    },

    applyFilters() {
      this.fetchUsers();
    },

    resetFilters() {
      this.filters = {
        search: '',
        role_id: '',
        company_id: '',
        active: ''
      };
      this.fetchUsers();
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.modal?.show();
    },

    editUser(user) {
      this.isEditing = true;
      this.form = {
        id: user.id,
        name: user.name,
        email: user.email,
        user_name: user.user_name,
        role_id: user.role_id,
        company_id: user.company_id,
        active: user.active,
        password: '',
        password_confirmation: ''
      };
      this.modal?.show();
    },

    openChangePasswordModal(user) {
      this.passwordForm = {
        id: user.id,
        password: '',
        password_confirmation: ''
      };
      this.passwordModal?.show();
    },

    async saveUser() {
      try {
        const data = { ...this.form };

        // Convertir active en entier
        data.active = data.active ? 1 : 0;

        if (this.isEditing) {
          // Ne pas envoyer password si vide lors de l'édition
          delete data.password;
          delete data.password_confirmation;

          await axios.put(`/users/${this.form.id}`, data);
          alert('Utilisateur modifié avec succès');
        } else {
          await axios.post('/users', data);
          alert('Utilisateur créé avec succès');
        }

        this.modal?.hide();
        this.fetchUsers();
      } catch (error) {
        console.error('Erreur:', error.response);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          let errorMessage = 'Erreurs:\n';
          for (let field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement');
        }
      }
    },

    async changePassword() {
      if (this.passwordForm.password !== this.passwordForm.password_confirmation) {
        alert('Les mots de passe ne correspondent pas');
        return;
      }

      try {
        await axios.put(`/users/${this.passwordForm.id}`, {
          password: this.passwordForm.password,
          password_confirmation: this.passwordForm.password_confirmation
        });

        alert('Mot de passe modifié avec succès');
        this.passwordModal?.hide();
      } catch (error) {
        console.error('Erreur:', error);
        alert(error.response?.data?.message || 'Erreur lors du changement de mot de passe');
      }
    },

    async deleteUser(id) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        return;
      }

      try {
        await axios.delete(`/users/${id}`);
        alert('Utilisateur supprimé avec succès');
        this.fetchUsers();
      } catch (error) {
        console.error('Erreur:', error);
        alert(error.response?.data?.message || 'Erreur lors de la suppression');
      }
    }
  }
};
</script>

<style scoped>
.badge {
  font-size: 0.85rem;
  padding: 0.35rem 0.5rem;
}
</style>
