<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('roles.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-secondary me-2" @click="fetchSystemRoles">
            <i class="fas fa-sync"></i> {{ $t('roles.system_roles') }}
          </button>
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('roles.new') }}
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Recherche -->
        <div class="row mb-3">
          <div class="col-md-4">
            <input
              type="text"
              class="form-control"
              :placeholder="$t('roles.search_placeholder')"
              v-model="search"
            />
          </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <!-- Tableau -->
        <div v-else class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th width="80">{{ $t('common.matricule') }}</th>
                <th>{{ $t('common.name') }}</th>
                <th>{{ $t('common.description') }}</th>
                <th width="200">{{ $t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredRoles.length === 0">
                <td colspan="4" class="text-center text-muted">
                  {{ $t('roles.no_found') }}
                </td>
              </tr>
              <tr v-for="role in filteredRoles" :key="role.role_id">
                <td>{{ role.role_id }}</td>
                <td>
                  <span class="badge bg-primary">{{ role.name }}</span>
                </td>
                <td>{{ role.description || '-' }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-info"
                    @click="viewRole(role.role_id)"
                    :title="$t('common.view_details')"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning ms-1"
                    @click="editRole(role)"
                    :title="$t('common.edit')"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteRole(role.role_id)"
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
    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? $t('roles.edit') : $t('roles.new') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRole">
              <div class="mb-3">
                <label for="role_name" class="form-label">{{ $t('roles.role_name') }} *</label>
                <input
                  type="text"
                  id="role_name"
                  class="form-control"
                  v-model="form.name"
                  :placeholder="$t('roles.role_name_placeholder')"
                  required
                  autofocus
                />
              </div>

              <div class="mb-3">
                <label for="role_description" class="form-label">{{ $t('common.description') }}</label>
                <textarea
                  id="role_description"
                  class="form-control"
                  v-model="form.description"
                  rows="3"
                  :placeholder="$t('roles.description_placeholder')"
                ></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" @click="saveRole">
              <i class="fas fa-save"></i> {{ $t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="roleDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('roles.details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedRole">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th width="150">{{ $t('common.matricule') }}</th>
                  <td>{{ selectedRole.role_id }}</td>
                </tr>
                <tr>
                  <th>{{ $t('common.name') }}</th>
                  <td><span class="badge bg-primary">{{ selectedRole.name }}</span></td>
                </tr>
                <tr>
                  <th>{{ $t('common.description') }}</th>
                  <td>{{ selectedRole.description || '-' }}</td>
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
  name: 'Roles',

  data() {
    return {
      roles: [],
      loading: false,
      search: '',
      isEditing: false,
      form: this.getEmptyForm(),
      modal: null,
      detailModal: null,
      selectedRole: null
    };
  },

  computed: {
    filteredRoles() {
      if (!this.search) return this.roles;

      const searchLower = this.search.toLowerCase();
      return this.roles.filter(role =>
        role.name.toLowerCase().includes(searchLower) ||
        (role.description && role.description.toLowerCase().includes(searchLower)) ||
        role.role_id.toString().includes(searchLower)
      );
    }
  },

  mounted() {
    this.fetchRoles();
    this.modal = new Modal(document.getElementById('roleModal'));
    this.detailModal = new Modal(document.getElementById('roleDetailModal'));
  },

  methods: {
    getEmptyForm() {
      return {
        role_id: null,
        name: '',
        description: ''
      };
    },

    async fetchRoles() {
      this.loading = true;
      try {
        const response = await axios.get('/roles');
        this.roles = response.data.data || response.data;
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des rôles');
      } finally {
        this.loading = false;
      }
    },

    async fetchSystemRoles() {
      try {
        const response = await axios.get('/roles/system');
        const systemRoles = response.data.data || response.data;
        alert(`Rôles système: ${systemRoles.map(r => r.name).join(', ')}`);
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des rôles système');
      }
    },

    async viewRole(id) {
      try {
        const response = await axios.get(`/roles/${id}`);
        this.selectedRole = response.data.data || response.data;
        this.detailModal.show();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement du rôle');
      }
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.modal.show();
    },

    editRole(role) {
      this.isEditing = true;
      this.form = { ...role };
      this.modal.show();
    },

    async saveRole() {
      if (!this.form.name || this.form.name.trim() === '') {
        alert('Le nom du rôle est requis');
        return;
      }

      try {
        const data = {
          name: this.form.name,
          description: this.form.description
        };

        if (this.isEditing) {
          await axios.put(`/roles/${this.form.role_id}`, data);
          alert('Rôle modifié avec succès');
        } else {
          await axios.post('/roles', data);
          alert('Rôle créé avec succès');
        }

        this.modal.hide();
        this.fetchRoles();
      } catch (error) {
        console.error('Erreur:', error.response);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          let errorMessage = 'Erreurs:\n';
          for (let field in errors) {
            errorMessage += `- ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement');
        }
      }
    },

    async deleteRole(id) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')) {
        return;
      }

      try {
        await axios.delete(`/roles/${id}`);
        alert('Rôle supprimé avec succès');
        this.fetchRoles();
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
  font-size: 0.9rem;
  padding: 0.4rem 0.6rem;
}
</style>
