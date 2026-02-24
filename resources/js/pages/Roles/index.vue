<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Gestion des Rôles</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-secondary me-2" @click="fetchSystemRoles">
            <i class="fas fa-sync"></i> Rôles Système
          </button>
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> Nouveau Rôle
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
              placeholder="Rechercher un rôle..."
              v-model="search"
            />
          </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
        </div>

        <!-- Tableau -->
        <div v-else class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th width="80">ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th width="200">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredRoles.length === 0">
                <td colspan="4" class="text-center text-muted">
                  Aucun rôle trouvé
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
                    title="Voir détails"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning ms-1"
                    @click="editRole(role)"
                    title="Modifier"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteRole(role.role_id)"
                    title="Supprimer"
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
              {{ isEditing ? 'Modifier le Rôle' : 'Nouveau Rôle' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRole">
              <div class="mb-3">
                <label for="role_name" class="form-label">Nom du Rôle *</label>
                <input
                  type="text"
                  id="role_name"
                  class="form-control"
                  v-model="form.name"
                  placeholder="Ex: Manager, Comptable..."
                  required
                  autofocus
                />
              </div>

              <div class="mb-3">
                <label for="role_description" class="form-label">Description</label>
                <textarea
                  id="role_description"
                  class="form-control"
                  v-model="form.description"
                  rows="3"
                  placeholder="Description du rôle..."
                ></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn btn-primary" @click="saveRole">
              <i class="fas fa-save"></i> Enregistrer
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
            <h5 class="modal-title">Détails du Rôle</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedRole">
            <table class="table table-bordered">
              <tr>
                <th width="150">ID</th>
                <td>{{ selectedRole.role_id }}</td>
              </tr>
              <tr>
                <th>Nom</th>
                <td><span class="badge bg-primary">{{ selectedRole.name }}</span></td>
              </tr>
              <tr>
                <th>Description</th>
                <td>{{ selectedRole.description || '-' }}</td>
              </tr>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
