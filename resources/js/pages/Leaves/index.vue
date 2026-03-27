<template>
  <div class="container-fluid mt-4">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ stats.pending }}</h3>
            <p>{{ $t('leaves.stats.pending') }}</p>
          </div>
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ stats.approved }}</h3>
            <p>{{ $t('leaves.stats.approved') }}</p>
          </div>
          <div class="icon">
            <i class="fas fa-check"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ stats.rejected }}</h3>
            <p>{{ $t('leaves.stats.rejected') }}</p>
          </div>
          <div class="icon">
            <i class="fas fa-times"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ stats.cancelled }}</h3>
            <p>{{ $t('leaves.stats.cancelled') }}</p>
          </div>
          <div class="icon">
            <i class="fas fa-ban"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Carte principale -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('leaves.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('leaves.new') }}
          </button>
          <button class="btn btn-sm btn-info ms-2" @click="showPending">
            <i class="fas fa-list"></i> {{ $t('leaves.pending_count', { count: stats.pending }) }}
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Filtres -->
        <div class="row mb-3">
          <div class="col-md-2">
            <label class="form-label">{{ $t('leaves.employee') }}</label>
            <select
              class="form-select"
              v-model="filters.employee_id"
              @change="applyFilters"
            >
              <option value="">{{ $t('leaves.all') }}</option>
              <option
                v-for="emp in employees"
                :key="emp.employee_id"
                :value="emp.employee_id"
              >
                {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ $t('leaves.type') }}</label>
            <select
              class="form-select"
              v-model="filters.leave_type"
              @change="applyFilters"
            >
              <option value="">{{ $t('leaves.all_types') }}</option>
              <option value="annual">{{ $t('leaves.types.annual') }}</option>
              <option value="sick">{{ $t('leaves.types.sick') }}</option>
              <option value="maternity">{{ $t('leaves.types.maternity') }}</option>
              <option value="unpaid">{{ $t('leaves.types.unpaid') }}</option>
              <option value="other">{{ $t('leaves.types.other') }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ $t('leaves.status') }}</label>
            <select class="form-select" v-model="filters.status" @change="applyFilters">
              <option value="">{{ $t('leaves.all_statuses') }}</option>
              <option value="pending">{{ $t('leaves.pending') }}</option>
              <option value="approved">{{ $t('leaves.approved') }}</option>
              <option value="rejected">{{ $t('leaves.rejected') }}</option>
              <option value="cancelled">{{ $t('leaves.cancelled') }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ $t('payroll.year') }}</label>
            <select class="form-select" v-model="filters.year" @change="applyFilters">
              <option value="">{{ $t('leaves.all_years') }}</option>
              <option value="2024">2024</option>
              <option value="2025">2025</option>
              <option value="2026">2026</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ $t('leaves.start_date') }}</label>
            <input
              type="date"
              class="form-control"
              v-model="filters.start_date"
              @change="applyFilters"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ $t('leaves.end_date') }}</label>
            <input
              type="date"
              class="form-control"
              v-model="filters.end_date"
              @change="applyFilters"
            />
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-12">
            <button class="btn btn-secondary btn-sm" @click="resetFilters">
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
                <th>{{ $t('leaves.employee') }}</th>
                <th>{{ $t('leaves.type') }}</th>
                <th>{{ $t('leaves.start_date') }}</th>
                <th>{{ $t('leaves.end_date') }}</th>
                <th>{{ $t('leaves.duration') }}</th>
                <th>{{ $t('leaves.status') }}</th>
                <th>{{ $t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="leaves.length === 0">
                <td colspan="8" class="text-center text-muted">{{ $t('leaves.no_found') }}</td>
              </tr>
              <tr v-for="leave in leaves" :key="leave.leave_id">
                <td>{{ leave.leave_id }}</td>
                <td>
                  <strong
                    >{{ leave.employee?.first_name }}
                    {{ leave.employee?.last_name }}</strong
                  >
                  <br />
                  <small class="text-muted">Mat: {{ leave.employee?.matricule }}</small>
                </td>
                <td>
                  <span :class="getTypeClass(leave.leave_type)">
                    {{ getTypeLabel(leave.leave_type) }}
                  </span>
                </td>
                <td>{{ formatDate(leave.start_date) }}</td>
                <td>{{ formatDate(leave.end_date) }}</td>
                <td>
                  <strong>{{ leave.duration_days }} {{ $t('common.days') }}</strong>
                </td>
                <td>
                  <span :class="getStatusClass(leave.status)">
                    {{ getStatusLabel(leave.status) }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-info"
                    @click="viewLeave(leave.leave_id)"
                    :title="$t('common.view_details')"
                  >
                    <i class="fas fa-eye"></i>
                  </button>

                  <!-- Approuver/Rejeter (si pending) -->
                  <template v-if="leave.status === 'pending'">
                    <button
                      class="btn btn-sm btn-success ms-1"
                      @click="approveLeave(leave.leave_id)"
                      :title="$t('leaves.approve')"
                    >
                      <i class="fas fa-check"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-danger ms-1"
                      @click="rejectLeave(leave.leave_id)"
                      :title="$t('leaves.reject')"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </template>

                  <!-- Modifier (si pending ou approved) -->
                  <button
                    v-if="['pending', 'approved'].includes(leave.status)"
                    class="btn btn-sm btn-warning ms-1"
                    @click="editLeave(leave)"
                    :title="$t('common.edit')"
                  >
                    <i class="fas fa-edit"></i>
                  </button>

                  <!-- Annuler (si pending ou approved) -->
                  <button
                    v-if="['pending', 'approved'].includes(leave.status)"
                    class="btn btn-sm btn-secondary ms-1"
                    @click="cancelLeave(leave.leave_id)"
                    :title="$t('leaves.cancel')"
                  >
                    <i class="fas fa-ban"></i>
                  </button>

                  <!-- Supprimer -->
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteLeave(leave.leave_id)"
                    :title="$t('common.delete')"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            {{ pagination.total }} conge(s) - Page {{ pagination.current_page }}/{{ pagination.last_page }}
          </small>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page <= 1 || loading" @click="changePage(pagination.current_page - 1)">Precedent</button>
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page >= pagination.last_page || loading" @click="changePage(pagination.current_page + 1)">Suivant</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="leaveModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? $t('leaves.edit') : $t('leaves.new') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">{{ $t('leaves.employee') }} *</label>
                <select
                  class="form-select"
                  v-model="form.employee_id"
                  required
                  :disabled="isEditing"
                >
                  <option value="">-- {{ $t('common.select') }} --</option>
                  <option
                    v-for="emp in employees"
                    :key="emp.employee_id"
                    :value="emp.employee_id"
                  >
                    {{ emp.first_name }} {{ emp.last_name }} ({{ emp.matricule }})
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">{{ $t('leaves.leave_type') }} *</label>
                <select class="form-select" v-model="form.leave_type" required>
                  <option value="annual">{{ $t('leaves.types.annual') }}</option>
                  <option value="sick">{{ $t('leaves.types.sick') }}</option>
                  <option value="maternity">{{ $t('leaves.types.maternity') }}</option>
                  <option value="unpaid">{{ $t('leaves.types.unpaid') }}</option>
                  <option value="other">{{ $t('leaves.types.other') }}</option>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('leaves.start_date') }} *</label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="form.start_date"
                    required
                    @change="calculateDuration"
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('leaves.end_date') }} *</label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="form.end_date"
                    required
                    @change="calculateDuration"
                  />
                </div>
              </div>

              <div v-if="calculatedDuration > 0" class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                {{ $t('leaves.duration_label', { count: calculatedDuration }) }}
              </div>

              <div class="mb-3" v-if="isEditing">
                <label class="form-label">{{ $t('leaves.status') }}</label>
                <select class="form-select" v-model="form.status">
                  <option value="pending">{{ $t('leaves.pending') }}</option>
                  <option value="approved">{{ $t('leaves.approved') }}</option>
                  <option value="rejected">{{ $t('leaves.rejected') }}</option>
                  <option value="cancelled">{{ $t('leaves.cancelled') }}</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" @click="saveLeave">
              {{ $t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="detailModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('leaves.details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedLeave">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th width="150">{{ $t('common.matricule') }}</th>
                  <td>{{ selectedLeave.leave_id }}</td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.employee') }}</th>
                  <td>
                    {{ selectedLeave.employee?.first_name }}
                    {{ selectedLeave.employee?.last_name }}
                    <br />
                    <small>Matricule: {{ selectedLeave.employee?.matricule }}</small>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.type') }}</th>
                  <td>
                    <span :class="getTypeClass(selectedLeave.leave_type)">
                      {{ getTypeLabel(selectedLeave.leave_type) }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.start_date') }}</th>
                  <td>{{ formatDate(selectedLeave.start_date) }}</td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.end_date') }}</th>
                  <td>{{ formatDate(selectedLeave.end_date) }}</td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.duration') }}</th>
                  <td>
                    <strong>{{ selectedLeave.duration_days }} {{ $t('common.days') }}</strong>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t('leaves.status') }}</th>
                  <td>
                    <span :class="getStatusClass(selectedLeave.status)">
                      {{ getStatusLabel(selectedLeave.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              {{ $t('common.close') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from "bootstrap";
import axios from 'axios'

export default {
  name: "Leaves",

  data() {
    return {
      leaves: [],
      employees: [],
      loading: false,
      isEditing: false,
      form: this.getEmptyForm(),
      filters: {
        employee_id: "",
        leave_type: "",
        status: "",
        year: "",
        start_date: "",
        end_date: "",
      },
      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1,
      },
      stats: {
        pending: 0,
        approved: 0,
        rejected: 0,
        cancelled: 0,
      },
      modal: null,
      detailModal: null,
      selectedLeave: null,
      calculatedDuration: 0,
    };
  },

  mounted() {
    this.fetchLeaves();
    this.fetchEmployees();
    this.modal = new Modal(document.getElementById("leaveModal"));
    this.detailModal = new Modal(document.getElementById("detailModal"));
  },

  methods: {
    getEmptyForm() {
      return {
        leave_id: null,
        employee_id: "",
        leave_type: "annual",
        start_date: "",
        end_date: "",
        status: "pending",
      };
    },

    async fetchLeaves() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        if (this.filters.employee_id)
          params.append("employee_id", this.filters.employee_id);
        if (this.filters.leave_type) params.append("leave_type", this.filters.leave_type);
        if (this.filters.status) params.append("status", this.filters.status);
        if (this.filters.year) params.append("year", this.filters.year);
        if (this.filters.start_date) params.append("start_date", this.filters.start_date);
        if (this.filters.end_date) params.append("end_date", this.filters.end_date);
        params.append("page", this.pagination.current_page);
        params.append("per_page", this.pagination.per_page);

        const response = await axios.get(`/leaves?${params.toString()}`);
        this.leaves = response.data.data || [];
        this.pagination = {
          ...this.pagination,
          ...(response.data?.meta || {}),
        };
        this.calculateStats();
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du chargement des congés");
      } finally {
        this.loading = false;
      }
    },

    async fetchEmployees() {
      try {
        const response = await axios.get("/employees?per_page=1000");
        this.employees = response.data.data || response.data;
      } catch (error) {
        console.error("Erreur:", error);
      }
    },

    calculateStats() {
      this.stats = {
        pending: this.leaves.filter((l) => l.status === "pending").length,
        approved: this.leaves.filter((l) => l.status === "approved").length,
        rejected: this.leaves.filter((l) => l.status === "rejected").length,
        cancelled: this.leaves.filter((l) => l.status === "cancelled").length,
      };
    },

    calculateDuration() {
      if (this.form.start_date && this.form.end_date) {
        const start = new Date(this.form.start_date);
        const end = new Date(this.form.end_date);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        this.calculatedDuration = diffDays;
      } else {
        this.calculatedDuration = 0;
      }
    },

    applyFilters() {
      this.pagination.current_page = 1;
      this.fetchLeaves();
    },

    resetFilters() {
      this.filters = {
        employee_id: "",
        leave_type: "",
        status: "",
        year: "",
        start_date: "",
        end_date: "",
      };
      this.pagination.current_page = 1;
      this.fetchLeaves();
    },

    showPending() {
      this.filters.status = "pending";
      this.pagination.current_page = 1;
      this.fetchLeaves();
    },
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchLeaves();
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.calculatedDuration = 0;
      this.modal.show();
    },

    editLeave(leave) {
      this.isEditing = true;
      this.form = {
        leave_id: leave.leave_id,
        employee_id: leave.employee_id,
        leave_type: leave.leave_type,
        start_date: leave.start_date,
        end_date: leave.end_date,
        status: leave.status,
      };
      this.calculateDuration();
      this.modal.show();
    },

    async viewLeave(id) {
      try {
        const response = await axios.get(`/leaves/${id}`);
        this.selectedLeave = response.data.data || response.data;
        this.detailModal.show();
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du chargement");
      }
    },

    async saveLeave() {
      try {
        if (this.isEditing) {
          await axios.put(`/leaves/${this.form.leave_id}`, this.form);
          alert("Congé modifié avec succès");
        } else {
          await axios.post("/leaves", this.form);
          alert("Demande de congé créée avec succès");
        }

        this.modal.hide();
        this.fetchLeaves();
      } catch (error) {
        console.error("Erreur:", error.response);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          let errorMessage = "Erreurs:\n";
          for (let field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(", ")}\n`;
          }
          alert(errorMessage);
        } else {
          alert(error.response?.data?.message || "Erreur lors de l'enregistrement");
        }
      }
    },

    async approveLeave(id) {
      if (!confirm("Approuver cette demande de congé ?")) return;

      try {
        await axios.post(`/leaves/${id}/approve`);
        alert("Congé approuvé");
        this.fetchLeaves();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    async rejectLeave(id) {
      if (!confirm("Rejeter cette demande de congé ?")) return;

      try {
        await axios.post(`/leaves/${id}/reject`);
        alert("Congé rejeté");
        this.fetchLeaves();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    async cancelLeave(id) {
      if (!confirm("Annuler ce congé ?")) return;

      try {
        await axios.post(`/leaves/${id}/cancel`);
        alert("Congé annulé");
        this.fetchLeaves();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    async deleteLeave(id) {
      if (!confirm("Supprimer définitivement ce congé ?")) return;

      try {
        await axios.delete(`/leaves/${id}`);
        alert("Congé supprimé");
        this.fetchLeaves();
      } catch (error) {
        alert("Erreur lors de la suppression");
      }
    },

    formatDate(date) {
      if (!date) return "-";
      return new Date(date).toLocaleDateString("fr-FR");
    },

    getTypeClass(type) {
      const classes = {
        annual: "badge bg-primary",
        sick: "badge bg-warning",
        maternity: "badge bg-info",
        unpaid: "badge bg-secondary",
        other: "badge bg-dark",
      };
      return classes[type] || "badge bg-secondary";
    },

    getTypeLabel(type) {
      return this.$t(`leaves.types.${type}`) || type;
    },

    getStatusClass(status) {
      const classes = {
        pending: "badge bg-warning",
        approved: "badge bg-success",
        rejected: "badge bg-danger",
        cancelled: "badge bg-secondary",
      };
      return classes[status] || "badge bg-secondary";
    },

    getStatusLabel(status) {
      return this.$t(`leaves.${status}`) || status;
    },
  },
};
</script>

<style scoped>
.badge {
  font-size: 0.85rem;
  padding: 0.35rem 0.5rem;
}

.small-box {
  border-radius: 0.25rem;
  position: relative;
  display: block;
  margin-bottom: 20px;
  box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
}

.small-box > .inner {
  padding: 10px;
}

.small-box .icon {
  color: rgba(0, 0, 0, 0.15);
  z-index: 0;
  position: absolute;
  top: -10px;
  right: 10px;
  font-size: 70px;
}

.small-box h3 {
  font-size: 2.2rem;
  font-weight: 700;
  margin: 0 0 10px;
  padding: 0;
  white-space: nowrap;
}
</style>
