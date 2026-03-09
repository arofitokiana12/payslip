<template>
  <div class="container-fluid mt-4">
    <!-- Carte de pointage rapide -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h4 class="card-title">
              <i class="fas fa-clock"></i> Pointage Rapide
            </h4>
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">Sélectionner un employé</label>
                <select class="form-select" v-model="quickCheckEmployee">
                  <option value="">-- Choisir --</option>
                  <option v-for="emp in employees" :key="emp.employee_id" :value="emp.employee_id">
                    {{ emp.first_name }} {{ emp.last_name }} ({{ emp.matricule }})
                  </option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end gap-2">
                <button
                  class="btn btn-success"
                  @click="quickCheckIn"
                  :disabled="!quickCheckEmployee"
                >
                  <i class="fas fa-sign-in-alt"></i> Arrivée
                </button>
                <button
                  class="btn btn-warning"
                  @click="quickCheckOut"
                  :disabled="!quickCheckEmployee"
                >
                  <i class="fas fa-sign-out-alt"></i> Départ
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Carte principale -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Gestion des Présences</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> Nouvelle Présence
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Filtres -->
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">Employé</label>
            <select class="form-select" v-model="filters.employee_id" @change="applyFilters">
              <option value="">Tous les employés</option>
              <option v-for="emp in employees" :key="emp.employee_id" :value="emp.employee_id">
                {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Date</label>
            <input
              type="date"
              class="form-control"
              v-model="filters.date"
              @change="applyFilters"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label">Date Début</label>
            <input
              type="date"
              class="form-control"
              v-model="filters.start_date"
              @change="applyFilters"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label">Date Fin</label>
            <input
              type="date"
              class="form-control"
              v-model="filters.end_date"
              @change="applyFilters"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select class="form-select" v-model="filters.status" @change="applyFilters">
              <option value="">Tous</option>
              <option value="Present">Présent</option>
              <option value="Late">En retard</option>
              <option value="Absent">Absent</option>
              <option value="Half-day">Demi-journée</option>
              <option value="Remote">Télétravail</option>
            </select>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-secondary w-100" @click="resetFilters">
              <i class="fas fa-redo"></i>
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
                <th>Matricule</th>
                <th>Employé</th>
                <th>Date</th>
                <th>Arrivée</th>
                <th>Départ</th>
                <th>Durée</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="attendances.length === 0">
                <td colspan="8" class="text-center text-muted">
                  Aucune présence trouvée
                </td>
              </tr>
              <tr v-for="att in attendances" :key="att.attendance_id">
                <td>{{ att.attendance_id }}</td>
                <td>
                  <strong>{{ att.employee?.first_name }} {{ att.employee?.last_name }}</strong>
                  <br>
                  <small class="text-muted">Mat: {{ att.employee?.matricule }}</small>
                </td>
                <td>{{ formatDate(att.date) }}</td>
                <td>
                  <span class="badge bg-success">
                    {{ formatTime(att.check_in) }}
                  </span>
                </td>
                <td>
                  <span v-if="att.check_out" class="badge bg-danger">
                    {{ formatTime(att.check_out) }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ calculateDuration(att.check_in, att.check_out) }}</td>
                <td>
                  <span :class="getStatusClass(att.status)">
                    {{ getStatusLabel(att.status) }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-info"
                    @click="viewAttendance(att.attendance_id)"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning ms-1"
                    @click="editAttendance(att)"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteAttendance(att.attendance_id)"
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
    <div class="modal fade" id="attendanceModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? 'Modifier la Présence' : 'Nouvelle Présence' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Employé *</label>
                <select class="form-select" v-model="form.employee_id" required :disabled="isEditing">
                  <option value="">-- Sélectionner --</option>
                  <option v-for="emp in employees" :key="emp.employee_id" :value="emp.employee_id">
                    {{ emp.first_name }} {{ emp.last_name }} ({{ emp.matricule }})
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Date *</label>
                <input
                  type="date"
                  class="form-control"
                  v-model="form.date"
                  required
                  :disabled="isEditing"
                />
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Heure d'arrivée *</label>
                  <input
                    type="time"
                    class="form-control"
                    v-model="form.check_in"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Heure de départ</label>
                  <input
                    type="time"
                    class="form-control"
                    v-model="form.check_out"
                  />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Statut *</label>
                <select class="form-select" v-model="form.status" required>
                  <option value="Present">Présent</option>
                  <option value="Late">En retard</option>
                  <option value="Absent">Absent</option>
                  <option value="Half-day">Demi-journée</option>
                  <option value="Remote">Télétravail</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" @click="saveAttendance">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="detailModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Détails de la Présence</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedAttendance">
            <table class="table table-bordered">
              <tr>
                <th>ID</th>
                <td>{{ selectedAttendance.attendance_id }}</td>
              </tr>
              <tr>
                <th>Employé</th>
                <td>
                  {{ selectedAttendance.employee?.first_name }}
                  {{ selectedAttendance.employee?.last_name }}
                  <br>
                  <small>Matricule: {{ selectedAttendance.employee?.matricule }}</small>
                </td>
              </tr>
              <tr>
                <th>Date</th>
                <td>{{ formatDate(selectedAttendance.date) }}</td>
              </tr>
              <tr>
                <th>Arrivée</th>
                <td>{{ formatTime(selectedAttendance.check_in) }}</td>
              </tr>
              <tr>
                <th>Départ</th>
                <td>{{ selectedAttendance.check_out ? formatTime(selectedAttendance.check_out) : 'Pas encore parti' }}</td>
              </tr>
              <tr>
                <th>Durée</th>
                <td>{{ calculateDuration(selectedAttendance.check_in, selectedAttendance.check_out) }}</td>
              </tr>
              <tr>
                <th>Statut</th>
                <td>
                  <span :class="getStatusClass(selectedAttendance.status)">
                    {{ getStatusLabel(selectedAttendance.status) }}
                  </span>
                </td>
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
  name: 'Attendance',

  data() {
    return {
      attendances: [],
      employees: [],
      loading: false,
      isEditing: false,
      quickCheckEmployee: '',
      form: this.getEmptyForm(),
      filters: {
        employee_id: '',
        date: '',
        start_date: '',
        end_date: '',
        status: ''
      },
      modal: null,
      detailModal: null,
      selectedAttendance: null
    };
  },

  mounted() {
    this.fetchAttendances();
    this.fetchEmployees();
    this.modal = new Modal(document.getElementById('attendanceModal'));
    this.detailModal = new Modal(document.getElementById('detailModal'));
  },

  methods: {
    getEmptyForm() {
      return {
        attendance_id: null,
        employee_id: '',
        date: new Date().toISOString().split('T')[0],
        check_in: '',
        check_out: '',
        status: 'Present'
      };
    },

    async fetchAttendances() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        if (this.filters.employee_id) params.append('employee_id', this.filters.employee_id);
        if (this.filters.date) params.append('date', this.filters.date);
        if (this.filters.start_date) params.append('start_date', this.filters.start_date);
        if (this.filters.end_date) params.append('end_date', this.filters.end_date);
        if (this.filters.status) params.append('status', this.filters.status);

        const response = await axios.get(`/attendance?${params.toString()}`);
        this.attendances = response.data.data || response.data;
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des présences');
      } finally {
        this.loading = false;
      }
    },

    async fetchEmployees() {
      try {
        const response = await axios.get('/employees');
        this.employees = response.data.data || response.data;
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async quickCheckIn() {
      try {
        const response = await axios.post('/attendance/check-in', {
          employee_id: this.quickCheckEmployee
        });
        alert(response.data.message);
        this.quickCheckEmployee = '';
        this.fetchAttendances();
      } catch (error) {
        alert(error.response?.data?.message || 'Erreur lors du pointage');
      }
    },

    async quickCheckOut() {
      try {
        const response = await axios.post('/attendance/check-out', {
          employee_id: this.quickCheckEmployee
        });
        alert(response.data.message);
        this.quickCheckEmployee = '';
        this.fetchAttendances();
      } catch (error) {
        alert(error.response?.data?.message || 'Erreur lors du pointage');
      }
    },

    applyFilters() {
      this.fetchAttendances();
    },

    resetFilters() {
      this.filters = {
        employee_id: '',
        date: '',
        start_date: '',
        end_date: '',
        status: ''
      };
      this.fetchAttendances();
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.modal.show();
    },

    editAttendance(att) {
      this.isEditing = true;
      this.form = {
        attendance_id: att.attendance_id,
        employee_id: att.employee_id,
        date: att.date,
        check_in: this.extractTime(att.check_in),
        check_out: att.check_out ? this.extractTime(att.check_out) : '',
        status: att.status
      };
      this.modal.show();
    },

    async viewAttendance(id) {
      try {
        const response = await axios.get(`/attendance/${id}`);
        this.selectedAttendance = response.data.data || response.data;
        this.detailModal.show();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement');
      }
    },

    async saveAttendance() {
      try {
        const data = {
          ...this.form,
          check_in: this.form.check_in + ':00',
          check_out: this.form.check_out ? this.form.check_out + ':00' : null
        };

        if (this.isEditing) {
          await axios.put(`/attendance/${this.form.attendance_id}`, data);
          alert('Présence modifiée avec succès');
        } else {
          await axios.post('/attendance', data);
          alert('Présence créée avec succès');
        }

        this.modal.hide();
        this.fetchAttendances();
      } catch (error) {
        console.error('Erreur:', error.response);
        alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement');
      }
    },

    async deleteAttendance(id) {
      if (!confirm('Supprimer cette présence ?')) return;

      try {
        await axios.delete(`/attendance/${id}`);
        alert('Présence supprimée');
        this.fetchAttendances();
      } catch (error) {
        alert('Erreur lors de la suppression');
      }
    },

    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('fr-FR');
    },

    formatTime(time) {
      if (!time) return '-';
      return time.substring(0, 5);
    },

    extractTime(datetime) {
      if (!datetime) return '';
      return datetime.substring(0, 5);
    },

    calculateDuration(checkIn, checkOut) {
      if (!checkIn || !checkOut) return '-';

      const start = new Date(`2000-01-01T${checkIn}`);
      const end = new Date(`2000-01-01T${checkOut}`);
      const diff = (end - start) / 1000 / 60; // minutes

      const hours = Math.floor(diff / 60);
      const minutes = Math.floor(diff % 60);

      return `${hours}h ${minutes}min`;
    },

    getStatusClass(status) {
      const classes = {
        'Present': 'badge bg-success',
        'Late': 'badge bg-warning',
        'Absent': 'badge bg-danger',
        'Half-day': 'badge bg-info',
        'Remote': 'badge bg-primary'
      };
      return classes[status] || 'badge bg-secondary';
    },

    getStatusLabel(status) {
      const labels = {
        'Present': 'Présent',
        'Late': 'En retard',
        'Absent': 'Absent',
        'Half-day': 'Demi-journée',
        'Remote': 'Télétravail'
      };
      return labels[status] || status;
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
