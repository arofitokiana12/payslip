<template>
  <div class="container-fluid mt-4">
    <!-- Header avec tabs -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">{{ $t('settings.title') }}</h3>
      </div>
      <div class="card-body p-0">
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs nav-tabs-modern" role="tablist">
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'general' }"
              @click="activeTab = 'general'"
            >
            
              <i class="fas fa-cog"></i>
              {{ $t('settings.general') }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'payroll' }"
              @click="activeTab = 'payroll'"
            >
              <i class="fas fa-money-bill-wave"></i>
              {{ $t('settings.payroll') }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'tax' }"
              @click="activeTab = 'tax'"
            >
              <i class="fas fa-percent"></i>
              {{ $t('settings.tax') }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'contributions' }"
              @click="activeTab = 'contributions'"
            >
              <i class="fas fa-hand-holding-usd"></i>
              {{ $t('settings.contributions') }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'holidays' }"
              @click="activeTab = 'holidays'"
            >
              <i class="fas fa-calendar-day"></i>
              {{ $t('settings.holidays') }}
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- GENERAL SETTINGS -->
      <div v-show="activeTab === 'general'" class="fade-in">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">{{ $t('settings.general') }}</h4>
            <div class="card-tools">
              <button class="btn btn-primary btn-sm" @click="saveGeneralSettings">
                <i class="fas fa-save"></i> {{ $t('common.save') }}
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label">{{ $t('settings.company_name') }}</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="generalSettings.company_name"
                  placeholder="Nom de votre entreprise"
                />
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label">{{ $t('settings.currency') }}</label>
                <select class="form-select" v-model="generalSettings.currency">
                  <option value="MGA">MGA - Ariary malgache</option>
                  <option value="EUR">EUR - Euro</option>
                  <option value="USD">USD - Dollar américain</option>
                </select>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label">Heures de travail par semaine</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="generalSettings.working_hours_per_week"
                />
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label">Jours travaillés par semaine</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="generalSettings.working_days_per_week"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PAYROLL SETTINGS -->
      <div v-show="activeTab === 'payroll'" class="fade-in">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">{{ $t('settings.payroll') }}</h4>
            <div class="card-tools">
              <button class="btn btn-primary btn-sm" @click="savePayrollSettings">
                <i class="fas fa-save"></i> {{ $t('common.save') }}
              </button>
            </div>
          </div>
          <div class="card-body">
            <h5 class="mb-4">Taux Heures Supplémentaires</h5>
            <div class="row">
              <div class="col-md-4 mb-4">
                <label class="form-label">Semaine (%)</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control"
                    v-model.number="payrollSettings.overtime_rate_weekday"
                  />
                  <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Ex: 130 = +30%</small>
              </div>
              <div class="col-md-4 mb-4">
                <label class="form-label">Weekend (%)</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control"
                    v-model.number="payrollSettings.overtime_rate_weekend"
                  />
                  <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Ex: 150 = +50%</small>
              </div>
              <div class="col-md-4 mb-4">
                <label class="form-label">Jours fériés (%)</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control"
                    v-model.number="payrollSettings.overtime_rate_holiday"
                  />
                  <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Ex: 200 = +100%</small>
              </div>
            </div>

            <h5 class="mb-4 mt-4">Abattement IRSA</h5>
            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label">Taux d'abattement (%)</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control"
                    v-model.number="payrollSettings.irsa_abatement_rate"
                  />
                  <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Abattement pour le calcul de l'IRSA (généralement 20%)</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAX BRACKETS -->
      <div v-show="activeTab === 'tax'" class="fade-in">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Tranches d'Imposition (IRSA)</h4>
            <div class="card-tools">
              <button class="btn btn-primary btn-sm" @click="openTaxBracketModal">
                <i class="fas fa-plus"></i> Nouvelle Tranche
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Ordre</th>
                    <th>Salaire Min</th>
                    <th>Salaire Max</th>
                    <th>Taux (%)</th>
                    <th>Montant Fixe</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="taxBrackets.length === 0">
                    <td colspan="7" class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                      Aucune tranche définie
                    </td>
                  </tr>
                  <tr v-for="bracket in taxBrackets" :key="bracket.bracket_id">
                    <td><strong>{{ bracket.order }}</strong></td>
                    <td>{{ formatCurrency(bracket.min_salary) }}</td>
                    <td>{{ bracket.max_salary ? formatCurrency(bracket.max_salary) : 'Illimité' }}</td>
                    <td>{{ bracket.tax_rate }}%</td>
                    <td>{{ formatCurrency(bracket.fixed_amount) }}</td>
                    <td>
                      <span :class="bracket.active ? 'badge badge-approved' : 'badge badge-cancelled'">
                        {{ bracket.active ? 'Actif' : 'Inactif' }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-warning btn-sm" @click="editTaxBracket(bracket)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" @click="deleteTaxBracket(bracket.bracket_id)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- SOCIAL CONTRIBUTIONS -->
      <div v-show="activeTab === 'contributions'" class="fade-in">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Cotisations Sociales</h4>
            <div class="card-tools">
              <button class="btn btn-primary btn-sm" @click="openContributionModal">
                <i class="fas fa-plus"></i> Nouvelle Cotisation
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Taux Employé</th>
                    <th>Taux Employeur</th>
                    <th>Plafond</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="contributions.length === 0">
                    <td colspan="7" class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                      Aucune cotisation définie
                    </td>
                  </tr>
                  <tr v-for="contrib in contributions" :key="contrib.contribution_id">
                    <td>
                      <strong>{{ contrib.name }}</strong>
                      <br>
                      <small class="text-muted">{{ contrib.description }}</small>
                    </td>
                    <td><code>{{ contrib.code }}</code></td>
                    <td><span class="badge bg-primary">{{ contrib.employee_rate }}%</span></td>
                    <td><span class="badge bg-secondary">{{ contrib.employer_rate }}%</span></td>
                    <td>{{ contrib.ceiling ? formatCurrency(contrib.ceiling) : 'Aucun' }}</td>
                    <td>
                      <span :class="contrib.active ? 'badge badge-approved' : 'badge badge-cancelled'">
                        {{ contrib.active ? 'Actif' : 'Inactif' }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-warning btn-sm" @click="editContribution(contrib)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" @click="deleteContribution(contrib.contribution_id)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- HOLIDAYS -->
      <div v-show="activeTab === 'holidays'" class="fade-in">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Jours Fériés</h4>
            <div class="card-tools">
              <button class="btn btn-success btn-sm me-2" @click="generateHolidays">
                <i class="fas fa-magic"></i> Générer 2026
              </button>
              <button class="btn btn-primary btn-sm" @click="openHolidayModal">
                <i class="fas fa-plus"></i> Nouveau Jour Férié
              </button>
            </div>
          </div>
          <div class="card-body">
            <!-- Filtre par année -->
            <div class="row mb-4">
              <div class="col-md-3">
                <select class="form-select" v-model="holidayYear" @change="fetchHolidays">
                  <option value="">Toutes les années</option>
                  <option value="2024">2024</option>
                  <option value="2025">2025</option>
                  <option value="2026">2026</option>
                  <option value="2027">2027</option>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6" v-for="holiday in holidays" :key="holiday.holiday_id">
                <div class="holiday-card mb-3">
                  <div class="holiday-date">
                    <div class="date-day">{{ formatDay(holiday.date) }}</div>
                    <div class="date-month">{{ formatMonth(holiday.date) }}</div>
                  </div>
                  <div class="holiday-content">
                    <h5>{{ holiday.name }}</h5>
                    <div class="holiday-meta">
                      <span class="badge badge-info">{{ holiday.year }}</span>
                      <span v-if="holiday.recurring" class="badge badge-approved ms-2">
                        <i class="fas fa-repeat"></i> Récurrent
                      </span>
                    </div>
                  </div>
                  <div class="holiday-actions">
                    <button class="btn btn-warning btn-sm" @click="editHoliday(holiday)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm ms-1" @click="deleteHoliday(holiday.holiday_id)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="holidays.length === 0" class="text-center text-muted py-5">
              <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
              Aucun jour férié trouvé
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tax Bracket -->
    <div class="modal fade" id="taxBracketModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditingTaxBracket ? 'Modifier la Tranche' : 'Nouvelle Tranche' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Ordre</label>
              <input type="number" class="form-control" v-model.number="taxBracketForm.order" />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Salaire Minimum</label>
                <input type="number" class="form-control" v-model.number="taxBracketForm.min_salary" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Salaire Maximum</label>
                <input type="number" class="form-control" v-model.number="taxBracketForm.max_salary" placeholder="Vide = Illimité" />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Taux (%)</label>
                <input type="number" step="0.01" class="form-control" v-model.number="taxBracketForm.tax_rate" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Montant Fixe</label>
                <input type="number" class="form-control" v-model.number="taxBracketForm.fixed_amount" />
              </div>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="taxBracketForm.active" id="bracketActive">
                <label class="form-check-label" for="bracketActive">Actif</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" @click="saveTaxBracket">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Contribution -->
    <div class="modal fade" id="contributionModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditingContribution ? 'Modifier la Cotisation' : 'Nouvelle Cotisation' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nom</label>
              <input type="text" class="form-control" v-model="contributionForm.name" />
            </div>
            <div class="mb-3">
              <label class="form-label">Code</label>
              <input type="text" class="form-control" v-model="contributionForm.code" :disabled="isEditingContribution" />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Taux Employé (%)</label>
                <input type="number" step="0.01" class="form-control" v-model.number="contributionForm.employee_rate" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Taux Employeur (%)</label>
                <input type="number" step="0.01" class="form-control" v-model.number="contributionForm.employer_rate" />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Plafond (optionnel)</label>
              <input type="number" class="form-control" v-model.number="contributionForm.ceiling" placeholder="Laisser vide si aucun plafond" />
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" rows="3" v-model="contributionForm.description"></textarea>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="contributionForm.active" id="contribActive">
                <label class="form-check-label" for="contribActive">Actif</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" @click="saveContribution">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Holiday -->
    <div class="modal fade" id="holidayModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditingHoliday ? 'Modifier le Jour Férié' : 'Nouveau Jour Férié' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nom</label>
              <input type="text" class="form-control" v-model="holidayForm.name" />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" v-model="holidayForm.date" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Année</label>
                <input type="number" class="form-control" v-model.number="holidayForm.year" />
              </div>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="holidayForm.recurring" id="holidayRecurring">
                <label class="form-check-label" for="holidayRecurring">
                  Récurrent (chaque année)
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" @click="saveHoliday">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'Settings',

  data() {
    return {
      activeTab: 'general',

      // General Settings
      generalSettings: {
        company_name: '',
        currency: 'MGA',
        working_hours_per_week: 40,
        working_days_per_week: 5
      },

      // Payroll Settings
      payrollSettings: {
        overtime_rate_weekday: 130,
        overtime_rate_weekend: 150,
        overtime_rate_holiday: 200,
        irsa_abatement_rate: 20
      },

      // Tax Brackets
      taxBrackets: [],
      taxBracketForm: this.getEmptyTaxBracketForm(),
      isEditingTaxBracket: false,
      taxBracketModal: null,

      // Contributions
      contributions: [],
      contributionForm: this.getEmptyContributionForm(),
      isEditingContribution: false,
      contributionModal: null,

      // Holidays
      holidays: [],
      holidayForm: this.getEmptyHolidayForm(),
      isEditingHoliday: false,
      holidayModal: null,
      holidayYear: '2025'
    };
  },

  mounted() {
    this.fetchAllSettings();
    this.taxBracketModal = new Modal(document.getElementById('taxBracketModal'));
    this.contributionModal = new Modal(document.getElementById('contributionModal'));
    this.holidayModal = new Modal(document.getElementById('holidayModal'));
  },

  methods: {
    // ========== FETCH DATA ==========
    async fetchAllSettings() {
      await this.fetchGeneralSettings();
      await this.fetchPayrollSettings();
      await this.fetchTaxBrackets();
      await this.fetchContributions();
      await this.fetchHolidays();
    },

    async fetchGeneralSettings() {
      try {
        const response = await axios.get('/settings/category/general');
        const settings = response.data.data;
        settings.forEach(setting => {
          if (this.generalSettings.hasOwnProperty(setting.key)) {
            this.generalSettings[setting.key] = setting.type === 'number' ? parseFloat(setting.value) : setting.value;
          }
        });
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchPayrollSettings() {
      try {
        const response = await axios.get('/settings/category/payroll');
        const settings = response.data.data;
        settings.forEach(setting => {
          if (this.payrollSettings.hasOwnProperty(setting.key)) {
            this.payrollSettings[setting.key] = parseFloat(setting.value);
          }
        });

        // Fetch tax settings
        const taxResponse = await axios.get('/settings/category/tax');
        const taxSettings = taxResponse.data.data;
        taxSettings.forEach(setting => {
          if (this.payrollSettings.hasOwnProperty(setting.key)) {
            this.payrollSettings[setting.key] = parseFloat(setting.value);
          }
        });
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchTaxBrackets() {
      try {
        const response = await axios.get('/settings/tax-brackets');
        this.taxBrackets = response.data.data;
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchContributions() {
      try {
        const response = await axios.get('/settings/contributions');
        this.contributions = response.data.data;
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchHolidays() {
      try {
        const params = this.holidayYear ? `?year=${this.holidayYear}` : '';
        const response = await axios.get(`/settings/holidays${params}`);
        this.holidays = response.data.data;
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    // ========== SAVE SETTINGS ==========
    async saveGeneralSettings() {
      try {
        const settings = [
          { key: 'company_name', value: this.generalSettings.company_name },
          { key: 'currency', value: this.generalSettings.currency },
          { key: 'working_hours_per_week', value: this.generalSettings.working_hours_per_week.toString() },
          { key: 'working_days_per_week', value: this.generalSettings.working_days_per_week.toString() }
        ];

        await axios.post('/settings/bulk-update', { settings });
        alert('Paramètres généraux enregistrés avec succès');
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
      }
    },

    async savePayrollSettings() {
      try {
        const settings = [
          { key: 'overtime_rate_weekday', value: this.payrollSettings.overtime_rate_weekday.toString() },
          { key: 'overtime_rate_weekend', value: this.payrollSettings.overtime_rate_weekend.toString() },
          { key: 'overtime_rate_holiday', value: this.payrollSettings.overtime_rate_holiday.toString() },
          { key: 'irsa_abatement_rate', value: this.payrollSettings.irsa_abatement_rate.toString() }
        ];

        await axios.post('/settings/bulk-update', { settings });
        alert('Paramètres de paie enregistrés avec succès');
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
      }
    },

    // ========== TAX BRACKETS ==========
    getEmptyTaxBracketForm() {
      return {
        bracket_id: null,
        min_salary: 0,
        max_salary: null,
        tax_rate: 0,
        fixed_amount: 0,
        order: 1,
        active: true
      };
    },

    openTaxBracketModal() {
      this.isEditingTaxBracket = false;
      this.taxBracketForm = this.getEmptyTaxBracketForm();
      this.taxBracketModal.show();
    },

    editTaxBracket(bracket) {
      this.isEditingTaxBracket = true;
      this.taxBracketForm = { ...bracket };
      this.taxBracketModal.show();
    },

    async saveTaxBracket() {
      try {
        if (this.isEditingTaxBracket) {
          await axios.put(`/settings/tax-brackets/${this.taxBracketForm.bracket_id}`, this.taxBracketForm);
          alert('Tranche modifiée avec succès');
        } else {
          await axios.post('/settings/tax-brackets', this.taxBracketForm);
          alert('Tranche créée avec succès');
        }
        this.taxBracketModal.hide();
        this.fetchTaxBrackets();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
      }
    },

    async deleteTaxBracket(id) {
      if (!confirm('Supprimer cette tranche ?')) return;
      try {
        await axios.delete(`/settings/tax-brackets/${id}`);
        alert('Tranche supprimée');
        this.fetchTaxBrackets();
      } catch (error) {
        alert('Erreur lors de la suppression');
      }
    },

    // ========== CONTRIBUTIONS ==========
    getEmptyContributionForm() {
      return {
        contribution_id: null,
        name: '',
        code: '',
        employee_rate: 0,
        employer_rate: 0,
        ceiling: null,
        active: true,
        description: ''
      };
    },

    openContributionModal() {
      this.isEditingContribution = false;
      this.contributionForm = this.getEmptyContributionForm();
      this.contributionModal.show();
    },

    editContribution(contribution) {
      this.isEditingContribution = true;
      this.contributionForm = { ...contribution };
      this.contributionModal.show();
    },

    async saveContribution() {
      try {
        if (this.isEditingContribution) {
          await axios.put(`/settings/contributions/${this.contributionForm.contribution_id}`, this.contributionForm);
          alert('Cotisation modifiée avec succès');
        } else {
          await axios.post('/settings/contributions', this.contributionForm);
          alert('Cotisation créée avec succès');
        }
        this.contributionModal.hide();
        this.fetchContributions();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
      }
    },

    async deleteContribution(id) {
      if (!confirm('Supprimer cette cotisation ?')) return;
      try {
        await axios.delete(`/settings/contributions/${id}`);
        alert('Cotisation supprimée');
        this.fetchContributions();
      } catch (error) {
        alert('Erreur lors de la suppression');
      }
    },

    // ========== HOLIDAYS ==========
    getEmptyHolidayForm() {
      return {
        holiday_id: null,
        name: '',
        date: '',
        year: new Date().getFullYear(),
        recurring: false
      };
    },

    openHolidayModal() {
      this.isEditingHoliday = false;
      this.holidayForm = this.getEmptyHolidayForm();
      this.holidayModal.show();
    },

    editHoliday(holiday) {
      this.isEditingHoliday = true;
      this.holidayForm = { ...holiday };
      this.holidayModal.show();
    },

    async saveHoliday() {
      try {
        if (this.isEditingHoliday) {
          await axios.put(`/settings/holidays/${this.holidayForm.holiday_id}`, this.holidayForm);
          alert('Jour férié modifié avec succès');
        } else {
          await axios.post('/settings/holidays', this.holidayForm);
          alert('Jour férié créé avec succès');
        }
        this.holidayModal.hide();
        this.fetchHolidays();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
      }
    },

    async deleteHoliday(id) {
      if (!confirm('Supprimer ce jour férié ?')) return;
      try {
        await axios.delete(`/settings/holidays/${id}`);
        alert('Jour férié supprimé');
        this.fetchHolidays();
      } catch (error) {
        alert('Erreur lors de la suppression');
      }
    },

    async generateHolidays() {
      if (!confirm('Générer les jours fériés récurrents pour 2026 ?')) return;
      try {
        const response = await axios.post('/settings/holidays/generate', { year: 2026 });
        alert(response.data.message);
        this.holidayYear = '2026';
        this.fetchHolidays();
      } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération');
      }
    },

    // ========== FORMATTERS ==========
    formatCurrency(value) {
      return new Intl.NumberFormat('fr-FR').format(value);
    },

    formatDay(date) {
      return new Date(date).getDate();
    },

    formatMonth(date) {
      const months = ['JAN', 'FÉV', 'MAR', 'AVR', 'MAI', 'JUN', 'JUL', 'AOÛ', 'SEP', 'OCT', 'NOV', 'DÉC'];
      return months[new Date(date).getMonth()];
    }
  }
};
</script>

<style scoped>
/* Modern Tabs */
.nav-tabs-modern {
  border-bottom: 2px solid var(--border-light);
  padding: 0 var(--space-6);
  background: var(--bg-secondary);
}

.nav-tabs-modern .nav-link {
  border: none;
  color: var(--text-secondary);
  padding: var(--space-4) var(--space-6);
  font-weight: 600;
  transition: all var(--transition-base);
  position: relative;
}

.nav-tabs-modern .nav-link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--gradient-purple);
  transform: scaleX(0);
  transition: transform var(--transition-base);
}

.nav-tabs-modern .nav-link:hover {
  color: var(--text-primary);
}

.nav-tabs-modern .nav-link.active {
  color: var(--brand-primary);
}

.nav-tabs-modern .nav-link.active::after {
  transform: scaleX(1);
}

.nav-tabs-modern .nav-link i {
  margin-right: var(--space-2);
}

/* Holiday Card */
.holiday-card {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-4);
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  transition: all var(--transition-base);
}

.holiday-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.holiday-date {
  width: 60px;
  height: 60px;
  background: var(--gradient-purple);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.date-day {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
}

.date-month {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
}

.holiday-content {
  flex: 1;
}

.holiday-content h5 {
  margin: 0 0 var(--space-2);
  font-size: 1rem;
  font-weight: 600;
}

.holiday-meta {
  display: flex;
  gap: var(--space-2);
}

.holiday-actions {
  display: flex;
  gap: var(--space-2);
}

/* Input Group */
.input-group-text {
  background: var(--bg-secondary);
  border: 2px solid var(--border-color);
  border-left: none;
  color: var(--text-secondary);
  font-weight: 600;
}

.input-group .form-control {
  border-right: none;
}

.input-group .form-control:focus + .input-group-text {
  border-color: var(--brand-primary);
}
</style>

