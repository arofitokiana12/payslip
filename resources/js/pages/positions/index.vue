<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('positions.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('positions.new') }}
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Loader -->
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <!-- Tableau -->
        <table v-else class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>{{ $t('common.matricule') }}</th>
              <th>{{ $t('positions.position_name') }}</th>
              <th>{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="positions.length === 0">
              <td colspan="3" class="text-center text-muted">Aucune position</td>
            </tr>
            <tr v-for="position in positions" :key="position.position_id">
              <td>{{ position.position_id }}</td>
              <td>{{ position.position_name }}</td>
              <td>
                <button class="btn btn-sm btn-info" @click="editPosition(position)">
                  <i class="fas fa-edit"></i> {{ $t('common.edit') }}
                </button>
                <button
                  class="btn btn-sm btn-danger ms-1"
                  @click="deletePosition(position.position_id)"
                >
                  <i class="fas fa-trash"></i> {{ $t('common.delete') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            {{ pagination.total }} position(s) - Page {{ pagination.current_page }}/{{ pagination.last_page }}
          </small>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page <= 1 || loading" @click="changePage(pagination.current_page - 1)">Precedent</button>
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page >= pagination.last_page || loading" @click="changePage(pagination.current_page + 1)">Suivant</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="positionModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? $t('positions.edit') : $t('positions.create') }} {{ $t('nav.positions') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="savePosition">
              <div class="mb-3">
                <label class="form-label">{{ $t('positions.position_name') }}</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.position_name"
                  required
                />
                <input type="hidden"
                  v-model="form.company_id"
                  value="1"
                  />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" @click="savePosition">
              {{ $t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from "bootstrap";
import axios from "axios";

export default {
  name: "Positions",
  data() {
    return {
      positions: [],
      loading: false,
      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1
      },
      isEditing: false,
      form: {
        position_id: null,
        position_name: "",
        company_id:1
      },
      modal: null,
    };
  },

  mounted() {
    this.fetchPositions();
    // Initialiser le modal Bootstrap
    this.modal = new Modal(document.getElementById("positionModal"));
  },

  methods: {
    async fetchPositions() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        params.append("page", this.pagination.current_page);
        params.append("per_page", this.pagination.per_page);
        const response = await axios.get(`/positions?${params.toString()}`);
        this.positions = response.data.data || [];
        this.pagination = {
          ...this.pagination,
          ...(response.data?.meta || {})
        };
      } catch (error) {
        console.error("Erreur lors du chargement des postes:", error);
        alert("Erreur lors du chargement des postes");
      } finally {
        this.loading = false;
      }
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = {
        position_id: null,
        position_name: "",
        company_id: 1
      };
      this.modal.show();
    },

    editPosition(position) {
      this.isEditing = true;
      this.form = { ...position };
      this.modal.show();
    },

    async savePosition() {
      try {
        if (this.isEditing) {
          // UPDATE
          await axios.put(`/positions/${this.form.position_id}`, this.form);
          alert("Poste modifié avec succès");
        } else {
          // CREATE
          await axios.post("/positions", this.form);
          alert("Position created successfully");
        }

        this.modal.hide();
        this.fetchPositions();
      } catch (error) {
        // Afficher les erreurs de validation
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          let errorMessage = "Erreurs de validation:\n";
          for (let field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(", ")}\n`;
          }
          alert(errorMessage);
        } else if (error.response && error.response.data && error.response.data.message) {
          alert(error.response.data.message);
        } else {
          alert("Erreur lors de l'enregistrement");
        }
      }
    },

    async deletePosition(id) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer ce poste ?")) return;

      try {
        await axios.delete(`/positions/${id}`);
        alert("Posistion deleted successfully");
        this.fetchPositions();
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors de la suppression");
      }
    },
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchPositions();
    },
  },
};
</script>
