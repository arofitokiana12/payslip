<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Positions</h3>
        <div class="card-tools">
          <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus"></i> New position
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Loader -->
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
        </div>

        <!-- Tableau -->
        <table v-else class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Position name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="position in positions" :key="position.position_id">
              <td>{{ position.position_id }}</td>
              <td>{{ position.position_name }}</td>
              <td>
                <button class="btn btn-sm btn-info" @click="editPosition(position)">
                  <i class="fas fa-edit"></i> Modifier
                </button>
                <button
                  class="btn btn-sm btn-danger ms-1"
                  @click="deletePosition(position.position_id)"
                >
                  <i class="fas fa-trash"></i> Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="positionModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? "Modifier" : "Créer" }} Poste</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="savePosition">
              <div class="mb-3">
                <label class="form-label">Nom du Poste</label>
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
              Annuler
            </button>
            <button type="button" class="btn btn-primary" @click="savePosition">
              Enregistrer
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
        const response = await axios.get("/positions");
        this.positions = response.data.data || response.data;
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
  },
};
</script>
