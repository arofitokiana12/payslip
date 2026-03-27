<template>
  <div class="container-fluid mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('companies.title') }}</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-primary" @click="openCreateModal">
            <i class="fas fa-plus"></i> {{ $t('companies.new') }}
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
              :placeholder="$t('companies.search_placeholder')"
              v-model="search"
            />
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="filterActive">
              <option value="">{{ $t('companies.all_statuses') }}</option>
              <option value="true">{{ $t('companies.active') }}</option>
              <option value="false">{{ $t('companies.inactive') }}</option>
            </select>
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
                <th width="60">{{ $t('common.matricule') }}</th>
                <th>{{ $t('companies.logo') }}</th>
                <th>{{ $t('companies.name') }}</th>
                <th>{{ $t('companies.email') }}</th>
                <th>{{ $t('companies.adress') }}</th>
                <th>{{ $t('companies.nif') }}</th>
                <th>{{ $t('companies.stat') }}</th>
                <th>{{ $t('companies.creation_date') }}</th>
                <th>{{ $t('companies.status') }}</th>
                <th width="200">{{ $t('companies.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredCompanies.length === 0">
                <td colspan="10" class="text-center text-muted">
                  {{ $t('companies.no_found') }}
                </td>
              </tr>
              <tr v-for="company in filteredCompanies" :key="company.company_id">
                <td>{{ company.company_id }}</td>
                <td>
                  <img
                    v-if="company.logo"
                    :src="company.logo"
                    alt="Logo"
                    class="img-thumbnail"
                    style="width: 40px; height: 40px; object-fit: cover;"
                  />
                  <span v-else class="text-muted">-</span>
                </td>
                <td>
                  <strong>{{ company.company_name }}</strong>
                </td>
                <td>{{ company.email || '-' }}</td>
                <td>{{ company.adress || '-' }}</td>
                <td>{{ company.nif || '-' }}</td>
                <td>{{ company.stat || '-' }}</td>
                <td>{{ formatDate(company.date_creation) }}</td>
                <td>
                  <span :class="company.active ? 'badge bg-success' : 'badge bg-danger'">
                    {{ company.active ? $t('companies.active') : $t('companies.inactive') }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-info"
                    @click="viewCompany(company.company_id)"
                    :title="$t('common.view_details')"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning ms-1"
                    @click="editCompany(company)"
                    :title="$t('common.edit')"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger ms-1"
                    @click="deleteCompany(company.company_id)"
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
            {{ pagination.total }} entreprise(s) - Page {{ pagination.current_page }}/{{ pagination.last_page }}
          </small>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page <= 1 || loading" @click="changePage(pagination.current_page - 1)">Precedent</button>
            <button class="btn btn-outline-secondary btn-sm" :disabled="pagination.current_page >= pagination.last_page || loading" @click="changePage(pagination.current_page + 1)">Suivant</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? $t('companies.edit') : $t('companies.new') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <!-- Informations de base -->
              <h6 class="border-bottom pb-2 mb-3">{{ $t('companies.general_info') }}</h6>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('companies.company_name_required') }}</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.company_name"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('companies.email') }}</label>
                  <input
                    type="email"
                    class="form-control"
                    v-model="form.email"
                  />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('companies.adress') }}</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.adress"
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('companies.creation_date') }}</label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="form.date_creation"
                  />
                </div>
              </div>

              <!-- Informations légales -->
              <h6 class="border-bottom pb-2 mb-3 mt-4">{{ $t('companies.legal_info') }}</h6>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{ $t('companies.nif') }}</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="form.nif"
                  />
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{ $t('companies.stat') }}</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="form.stat"
                  />
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{ $t('companies.rcs') }}</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="form.rcs"
                  />
                </div>
              </div>

              <!-- Logo et statut -->
              <h6 class="border-bottom pb-2 mb-3 mt-4">{{ $t('companies.other') }}</h6>

              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label">{{ $t('companies.logo_url') }}</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.logo"
                    :placeholder="$t('companies.logo_url_placeholder')"
                  />
                  <small class="text-muted">{{ $t('companies.logo_url_hint') }}</small>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{ $t('companies.status') }}</label>
                  <select class="form-select" v-model="form.active">
                    <option :value="true">{{ $t('companies.active') }}</option>
                    <option :value="false">{{ $t('companies.inactive') }}</option>
                  </select>
                </div>
              </div>

              <!-- Aperçu du logo -->
              <div v-if="form.logo" class="mb-3">
                <label class="form-label">{{ $t('companies.logo_preview') }}</label>
                <div>
                  <img
                    :src="form.logo"
                    alt="Logo preview"
                    class="img-thumbnail"
                    style="max-width: 150px; max-height: 150px;"
                    @error="logoError = true"
                  />
                  <p v-if="logoError" class="text-danger small mt-1">
                    {{ $t('companies.load_image_error') }}
                  </p>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" @click="saveCompany">
              <i class="fas fa-save"></i> {{ $t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="companyDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('companies.details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedCompany">
            <div class="row">
              <div class="col-md-3 text-center">
                <img
                  v-if="selectedCompany.logo"
                  :src="selectedCompany.logo"
                  alt="Logo"
                  class="img-thumbnail mb-3"
                  style="max-width: 150px;"
                />
                <h5>{{ selectedCompany.company_name }}</h5>
                <span :class="selectedCompany.active ? 'badge bg-success' : 'badge bg-danger'">
                  {{ selectedCompany.active ? $t('companies.active') : $t('companies.inactive') }}
                </span>
              </div>
              <div class="col-md-9">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th width="180">{{ $t('common.matricule') }}</th>
                      <td>{{ selectedCompany.company_id }}</td>
                    </tr>
                    <tr>
                      <th>Email</th>
                      <td>{{ selectedCompany.email || '-' }}</td>
                    </tr>
                    <tr>
                      <th>Adresse</th>
                      <td>{{ selectedCompany.adress || '-' }}</td>
                    </tr>
                    <tr>
                      <th>Date de Création</th>
                      <td>{{ formatDate(selectedCompany.date_creation) }}</td>
                    </tr>
                    <tr>
                      <th>NIF</th>
                      <td>{{ selectedCompany.nif || '-' }}</td>
                    </tr>
                    <tr>
                      <th>STAT</th>
                      <td>{{ selectedCompany.stat || '-' }}</td>
                    </tr>
                    <tr>
                      <th>RCS</th>
                      <td>{{ selectedCompany.rcs || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
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
  name: 'Companies',

  data() {
    return {
      companies: [],
      loading: false,
      search: '',
      filterActive: '',
      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1
      },
      isEditing: false,
      form: this.getEmptyForm(),
      modal: null,
      detailModal: null,
      selectedCompany: null,
      logoError: false
    };
  },

  computed: {
    filteredCompanies() {
      let result = this.companies;

      // Filtre par statut
      if (this.filterActive !== '') {
        const isActive = this.filterActive === 'true';
        result = result.filter(c => c.active === isActive);
      }

      // Filtre par recherche
      if (this.search) {
        const searchLower = this.search.toLowerCase();
        result = result.filter(c =>
          c.company_name.toLowerCase().includes(searchLower) ||
          (c.email && c.email.toLowerCase().includes(searchLower)) ||
          (c.adress && c.adress.toLowerCase().includes(searchLower)) ||
          c.company_id.toString().includes(searchLower)
        );
      }

      return result;
    }
  },

  mounted() {
    this.fetchCompanies();
    this.modal = new Modal(document.getElementById('companyModal'));
    this.detailModal = new Modal(document.getElementById('companyDetailModal'));
  },

  methods: {
    getEmptyForm() {
      return {
        company_id: null,
        company_name: '',
        date_creation: null,
        nif: null,
        stat: null,
        rcs: null,
        logo: '',
        adress: '',
        email: '',
        active: true
      };
    },

    async fetchCompanies() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        params.append('page', this.pagination.current_page);
        params.append('per_page', this.pagination.per_page);
        const response = await axios.get(`/companies?${params.toString()}`);
        this.companies = response.data.data || [];
        this.pagination = {
          ...this.pagination,
          ...(response.data?.meta || {})
        };
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des entreprises');
      } finally {
        this.loading = false;
      }
    },

    async viewCompany(id) {
      try {
        const response = await axios.get(`/companies/${id}`);
        this.selectedCompany = response.data.data || response.data;
        this.detailModal.show();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de l\'entreprise');
      }
    },

    openCreateModal() {
      this.isEditing = false;
      this.form = this.getEmptyForm();
      this.logoError = false;
      this.modal.show();
    },

    editCompany(company) {
      this.isEditing = true;
      this.logoError = false;
      this.form = {
        company_id: company.company_id,
        company_name: company.company_name,
        date_creation: company.date_creation,
        nif: company.nif,
        stat: company.stat,
        rcs: company.rcs,
        logo: company.logo,
        adress: company.adress,
        email: company.email,
        active: company.active
      };
      this.modal.show();
    },

    async saveCompany() {
      if (!this.form.company_name || this.form.company_name.trim() === '') {
        alert('Le nom de l\'entreprise est requis');
        return;
      }

      try {
        const data = {
          ...this.form,
          active: this.form.active ? 1 : 0
        };

        if (this.isEditing) {
          await axios.put(`/companies/${this.form.company_id}`, data);
          alert('Entreprise modifiée avec succès');
        } else {
          await axios.post('/companies', data);
          alert('Entreprise créée avec succès');
        }

        this.modal.hide();
        this.fetchCompanies();
      } catch (error) {
        console.error('Erreur:', error.response);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          let errorMessage = 'Erreurs de validation:\n';
          for (let field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement');
        }
      }
    },

    async deleteCompany(id) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?\n\nAttention: Tous les utilisateurs et employés liés seront également affectés.')) {
        return;
      }

      try {
        await axios.delete(`/companies/${id}`);
        alert('Entreprise supprimée avec succès');
        this.fetchCompanies();
      } catch (error) {
        console.error('Erreur:', error);

        if (error.response?.status === 409) {
          alert('Impossible de supprimer cette entreprise car des utilisateurs ou employés y sont rattachés.');
        } else {
          alert(error.response?.data?.message || 'Erreur lors de la suppression');
        }
      }
    },

    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('fr-FR');
    },
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchCompanies();
    }
  }
};
</script>

<style scoped>
.img-thumbnail {
  border-radius: 4px;
}

.badge {
  font-size: 0.85rem;
  padding: 0.35rem 0.5rem;
}

h6 {
  color: #495057;
  font-weight: 600;
}
</style>
