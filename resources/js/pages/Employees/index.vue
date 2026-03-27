<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('employees.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('employees.new') }}
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
              :placeholder="$t('employees.search_placeholder')"
              v-model="search"
              @input="applyFilters"
            />
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
                <th>{{ $t('employees.last_name')}}</th>
                <th>{{ $t('employees.first_name')}}</th>
                <th>{{ $t('employees.position')}}</th>
                <th>{{ $t('employees.hire_date')}}</th>
                <th>{{ $t('employees.contract_type')}}</th>
                <th>{{ $t('employees.base_salary')}}</th>
                <th>{{ $t('employees.status')}}</th>
                <th>{{ $t('common.actions')}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="employees.length === 0">
                <td colspan="9" class="text-center text-muted">{{ $t('employees.no_found') }}</td>
              </tr>
              <tr v-for="employee in employees" :key="employee.employee_id">
                <td>{{ employee.matricule }}</td>
                <td>{{ employee.last_name }}</td>
                <td>{{ employee.first_name }}</td>
                <td>{{ employee.position?.position_name || '-' }}</td>
                <td>{{ formatDate(employee.hire_date) }}</td>
                <td>{{ employee.contract_type }}</td>
                <td>{{ formatCurrency(employee.base_salary) }}</td>
                <td>
                  <span :class="getStatusClass(employee.status)">
                    {{ employee.status }}
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-info" @click="editEmployee(employee)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger ms-1" @click="deleteEmployee(employee.employee_id)">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            {{ pagination.total }} employe(s) - Page {{ pagination.current_page }}/{{ pagination.last_page }}
          </small>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page <= 1 || loading" @click="changePage(pagination.current_page - 1)">Precedent</button>
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page >= pagination.last_page || loading" @click="changePage(pagination.current_page + 1)">Suivant</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="employeeModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? $t('employees.edit') : $t('employees.new') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.first_name') }} *</label>
                  <input type="text" class="form-control" v-model="form.first_name" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.last_name') }} *</label>
                  <input type="text" class="form-control" v-model="form.last_name" required />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('common.matricule') }} *</label>
                  <input type="text" class="form-control" v-model="form.matricule" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.position') }} *</label>
                  <select class="form-select" v-model="form.position_id" required>
                    <option value="">-- Sélectionner --</option>
                    <option v-for="pos in positions" :key="pos.position_id" :value="pos.position_id">
                      {{ pos.position_name }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.hire_date') }} *</label>
                  <input type="date" class="form-control" v-model="form.hire_date" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.contract_type') }} *</label>
                  <select class="form-select" v-model="form.contract_type" required>
                    <option value="CDI">CDI</option>
                    <option value="CDD">CDD</option>
                    <option value="stage">Stage</option>
                    <option value="Freelance">Freelance</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.contract_end_date') }}</label>
                  <input type="date" class="form-control" v-model="form.contract_end_date" />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.base_salary') }} *</label>
                  <input type="number" step="0.01" class="form-control" v-model="form.base_salary" required />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.status') }}</label>
                  <select class="form-select" v-model="form.status">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                    <option value="on_leave">En congé</option>
                    <option value="syspended">Suspendu</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('employees.active') }}</label>
                  <select class="form-select" v-model="form.active">
                    <option :value="true">Oui</option>
                    <option :value="false">Non</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('common.cancel') }}</button>
            <button type="button" class="btn btn-primary" @click="saveEmployee">{{ $t('common.save') }}</button>
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
  name: 'Employees',
  data() {
    return {
      employees: [],
      positions: [],
      loading: false,
      search: '',
      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1
      },
      isEditing: false,
      form: this.getEmptyForm(),
      modal: null
    };
  },

  mounted() {
    this.fetchEmployees();
    this.fetchPositions();
    this.modal = new Modal(document.getElementById('employeeModal'));
  },

  methods: {
    getEmptyForm() {
      return {
        employee_id: null,
        first_name: '',
        last_name: '',
        matricule: '',
        position_id: '',
        hire_date: '',
        contract_type: 'CDI',
        contract_end_date: null,
        status: 'Actif',
        active: true,
        base_salary: 0,
        company_id: 1
      };
    },

    async fetchEmployees() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        if (this.search) params.append('search', this.search);
        params.append('page', this.pagination.current_page);
        params.append('per_page', this.pagination.per_page);

        const response = await axios.get(`/employees?${params.toString()}`);
        this.employees = response.data.data || [];
        this.pagination = {
          ...this.pagination,
          ...(response.data?.meta || {})
        };
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des employés');
      } finally {
        this.loading = false;
      }
    },

    async fetchPositions() {
      try {
        const response = await axios.get('/positions?per_page=1000');
        this.positions = response.data.data || response.data;
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    applyFilters() {
      this.pagination.current_page = 1;
      this.fetchEmployees();
    },

    changePage(page) {
      this.pagination.current_page = page;
      this.fetchEmployees();
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.modal.show();
    },

    editEmployee(employee) {
      this.isEditing = true;
      this.form = { ...employee };
      this.modal.show();
    },

   async saveEmployee() {
  try {
    if (this.isEditing) {
      await axios.put(`/employees/${this.form.employee_id}`, this.form);
      alert('Employé modifié avec succès');
    } else {
      await axios.post('/employees', this.form);
      alert('Employé créé avec succès');
    }

    this.modal.hide();
    this.fetchEmployees();
  } catch (error) {
    console.error('Erreur complète:', error.response); // ← AJOUTEZ CECI

    // Afficher les erreurs de validation
    if (error.response && error.response.data && error.response.data.errors) {
      const errors = error.response.data.errors;
      let errorMessage = 'Erreurs de validation:\n';
      for (let field in errors) {
        errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
      }
      alert(errorMessage);
    } else if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message);
    } else {
      alert('Erreur lors de l\'enregistrement');
    }
  }
},
    async deleteEmployee(id) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cet employé ?')) return;

      try {
        await axios.delete(`/employees/${id}`);
        alert('Employé supprimé avec succès');
        this.fetchEmployees();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression');
      }
    },

    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('fr-FR');
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'MGA' // ou 'EUR' selon votre devise
      }).format(amount);
    },

    getStatusClass(status) {
      const classes = {
        'Actif': 'badge bg-success',
        'En congé': 'badge bg-warning',
        'Suspendu': 'badge bg-danger'
      };
      return classes[status] || 'badge bg-secondary';
    }
  }
};
</script>
