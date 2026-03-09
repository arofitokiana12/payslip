<!-- resources/js/pages/Payroll/index.vue -->
<template>
  <div class="container-fluid mt-4">
    <!-- Header avec période sélection -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">💰 Gestion de la Paie</h3>
        <div class="card-tools">
          <div class="d-flex gap-3 align-items-center">
            <!-- Sélecteur de période -->
            <select class="form-select" v-model="selectedMonth" @change="fetchPayslips">
              <option value="1">Janvier</option>
              <option value="2">Février</option>
              <option value="3">Mars</option>
              <option value="4">Avril</option>
              <option value="5">Mai</option>
              <option value="6">Juin</option>
              <option value="7">Juillet</option>
              <option value="8">Août</option>
              <option value="9">Septembre</option>
              <option value="10">Octobre</option>
              <option value="11">Novembre</option>
              <option value="12">Décembre</option>
            </select>
            <select class="form-select" v-model="selectedYear" @change="fetchPayslips">
              <option value="2024">2024</option>
              <option value="2025">2025</option>
              <option value="2026">2026</option>
            </select>
            <button class="btn btn-primary" @click="showGenerateModal">
              <i class="fas fa-magic"></i> Générer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card pending">
        <div class="stat-header">
          <div class="stat-content">
            <h3>{{ payslips.filter((p) => p.status === "draft").length }}</h3>
            <p>Brouillons</p>
          </div>
          <div class="stat-icon">
            <i class="fas fa-file-alt"></i>
          </div>
        </div>
      </div>

      <div class="stat-card approved">
        <div class="stat-header">
          <div class="stat-content">
            <h3>{{ payslips.filter((p) => p.status === "finalized").length }}</h3>
            <p>Finalisées</p>
          </div>
          <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-content">
            <h3>{{ formatCurrency(totalGross) }}</h3>
            <p>Total Brut</p>
          </div>
          <div class="stat-icon">
            <i class="fas fa-coins"></i>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-content">
            <h3>{{ formatCurrency(totalNet) }}</h3>
            <p>Total Net</p>
          </div>
          <div class="stat-icon">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des fiches -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Fiches de Paie</h4>
      </div>
      <div class="card-body">
        <!-- Filtres -->
        <div class="row mb-4">
          <div class="col-md-3">
            <select class="form-select" v-model="filterEmployee" @change="applyFilters">
              <option value="">Tous les employés</option>
              <option
                v-for="emp in employees"
                :key="emp.employee_id"
                :value="emp.employee_id"
              >
                {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="filterStatus" @change="applyFilters">
              <option value="">Tous les statuts</option>
              <option value="draft">Brouillon</option>
              <option value="finalized">Finalisé</option>
              <option value="paid">Payé</option>
            </select>
          </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border" role="status"></div>
        </div>

        <!-- Tableau -->
        <div v-else class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Employé</th>
                <th>Période</th>
                <th>Salaire Brut</th>
                <th>Déductions</th>
                <th>Salaire Net</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredPayslips.length === 0">
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3"></i>
                  Aucune fiche de paie trouvée
                </td>
              </tr>
              <tr
                v-for="payslip in filteredPayslips"
                :key="payslip.payslip_id"
                class="fade-in"
              >
                <td>
                  <div>
                    <strong
                      >{{ payslip.employee?.first_name }}
                      {{ payslip.employee?.last_name }}</strong
                    >
                    <br />
                    <small class="text-muted"
                      >Mat: {{ payslip.employee?.matricule }}</small
                    >
                  </div>
                </td>
                <td>
                  {{ getMonthName(payslip.period_month) }} {{ payslip.period_year }}
                </td>
                <td>
                  <strong>{{ formatCurrency(payslip.gross_salary) }}</strong>
                </td>
                <td>
                  <span class="text-danger">{{
                    formatCurrency(payslip.total_deductions)
                  }}</span>
                </td>
                <td>
                  <strong class="text-success">{{
                    formatCurrency(payslip.net_salary)
                  }}</strong>
                </td>
                <td>
                  <span :class="'badge badge-' + getStatusClass(payslip.status)">
                    {{ getStatusLabel(payslip.status) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button
                      class="btn btn-info btn-sm"
                      @click="viewPayslip(payslip.payslip_id)"
                      title="Voir détails"
                    >
                      <i class="fas fa-eye"></i>
                    </button>

                    <button
                      v-if="payslip.status === 'draft'"
                      class="btn btn-success btn-sm"
                      @click="finalizePayslip(payslip.payslip_id)"
                      title="Finaliser"
                    >
                      <i class="fas fa-check"></i>
                    </button>

                    <button
                      v-if="payslip.status === 'finalized'"
                      class="btn btn-warning btn-sm"
                      @click="markAsPaid(payslip.payslip_id)"
                      title="Marquer payé"
                    >
                      <i class="fas fa-money-bill-wave"></i>
                    </button>

                    <button
                      class="btn btn-primary btn-sm"
                      @click="downloadPayslip(payslip.payslip_id)"
                      title="Télécharger PDF"
                    >
                      <i class="fas fa-download"></i>
                    </button>

                    <button
                      v-if="payslip.status === 'draft'"
                      class="btn btn-danger btn-sm"
                      @click="deletePayslip(payslip.payslip_id)"
                      title="Supprimer"
                    >
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

    <!-- Modal Génération -->
    <div class="modal fade" id="generateModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Générer les Fiches de Paie</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <h6>Période</h6>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Mois</label>
                  <select class="form-select" v-model="generateForm.month">
                    <option value="1">Janvier</option>
                    <option value="2">Février</option>
                    <option value="3">Mars</option>
                    <option value="4">Avril</option>
                    <option value="5">Mai</option>
                    <option value="6">Juin</option>
                    <option value="7">Juillet</option>
                    <option value="8">Août</option>
                    <option value="9">Septembre</option>
                    <option value="10">Octobre</option>
                    <option value="11">Novembre</option>
                    <option value="12">Décembre</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Année</label>
                  <select class="form-select" v-model="generateForm.year">
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6>Employés</h6>
              <div class="form-check mb-3">
                <input
                  class="form-check-input"
                  type="radio"
                  v-model="generateMode"
                  value="all"
                  id="modeAll"
                />
                <label class="form-check-label" for="modeAll">
                  Tous les employés actifs
                </label>
              </div>
              <div class="form-check mb-3">
                <input
                  class="form-check-input"
                  type="radio"
                  v-model="generateMode"
                  value="single"
                  id="modeSingle"
                />
                <label class="form-check-label" for="modeSingle"> Un seul employé </label>
              </div>

              <div v-if="generateMode === 'single'" class="mt-3">
                <select class="form-select" v-model="generateForm.employee_id">
                  <option value="">-- Sélectionner un employé --</option>
                  <option
                    v-for="emp in employees"
                    :key="emp.employee_id"
                    :value="emp.employee_id"
                  >
                    {{ emp.first_name }} {{ emp.last_name }} ({{ emp.matricule }})
                  </option>
                </select>
              </div>
            </div>

            <div
              v-if="generateMode === 'single' && generateForm.employee_id"
              class="alert alert-info"
            >
              <h6>Prévisualisation</h6>
              <button class="btn btn-sm btn-primary" @click="previewCalculation">
                <i class="fas fa-calculator"></i> Calculer
              </button>

              <div v-if="calculationPreview" class="mt-3">
                <table class="table table-sm">
                  <tr>
                    <td><strong>Salaire de base:</strong></td>
                    <td class="text-end">
                      {{ formatCurrency(calculationPreview.earnings.base_salary) }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Heures supplémentaires:</strong></td>
                    <td class="text-end">
                      {{ formatCurrency(calculationPreview.earnings.overtime) }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Bonus:</strong></td>
                    <td class="text-end">
                      {{ formatCurrency(calculationPreview.earnings.bonuses) }}
                    </td>
                  </tr>
                  <tr class="table-primary">
                    <td><strong>Total Brut:</strong></td>
                    <td class="text-end">
                      <strong>{{
                        formatCurrency(calculationPreview.gross_salary)
                      }}</strong>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Déductions:</strong></td>
                    <td class="text-end text-danger">
                      -{{ formatCurrency(calculationPreview.deductions.total) }}
                    </td>
                  </tr>
                  <tr class="table-success">
                    <td><strong>Salaire Net:</strong></td>
                    <td class="text-end">
                      <strong>{{ formatCurrency(calculationPreview.net_salary) }}</strong>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Annuler
            </button>
            <button type="button" class="btn btn-primary" @click="generatePayslips">
              <i class="fas fa-magic"></i> Générer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="detailModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Détails de la Fiche de Paie</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedPayslip">
            <!-- En-tête fiche -->
            <div class="payslip-header mb-4">
              <div class="row">
                <div class="col-md-6">
                  <h4>
                    {{ selectedPayslip.employee?.first_name }}
                    {{ selectedPayslip.employee?.last_name }}
                  </h4>
                  <p class="text-muted">
                    Matricule: {{ selectedPayslip.employee?.matricule }}<br />
                    Poste: {{ selectedPayslip.employee?.position?.position_name }}
                  </p>
                </div>
                <div class="col-md-6 text-end">
                  <h5>
                    {{ getMonthName(selectedPayslip.period_month) }}
                    {{ selectedPayslip.period_year }}
                  </h5>
                  <span :class="'badge badge-' + getStatusClass(selectedPayslip.status)">
                    {{ getStatusLabel(selectedPayslip.status) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Détails -->
            <div class="row">
              <!-- Gains -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header bg-success text-white">
                    <h6 class="mb-0">💰 Gains</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-sm">
                      <tbody>
                        <tr
                          v-for="item in selectedPayslip.items?.filter(
                            (i) => i.item_type === 'earning'
                          )"
                          :key="item.payslip_item_id"
                        >
                          <td>{{ item.item_name }}</td>
                          <td class="text-end">{{ formatCurrency(item.amount) }}</td>
                        </tr>
                        <tr class="table-success">
                          <td><strong>Total Gains</strong></td>
                          <td class="text-end">
                            <strong>{{
                              formatCurrency(selectedPayslip.total_earnings)
                            }}</strong>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Déductions -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">📉 Déductions & Impôts</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-sm">
                      <tbody>
                        <tr
                          v-for="item in selectedPayslip.items?.filter((i) =>
                            ['deduction', 'tax'].includes(i.item_type)
                          )"
                          :key="item.payslip_item_id"
                        >
                          <td>{{ item.item_name }}</td>
                          <td class="text-end text-danger">
                            -{{ formatCurrency(item.amount) }}
                          </td>
                        </tr>
                        <tr class="table-danger">
                          <td><strong>Total Déductions</strong></td>
                          <td class="text-end">
                            <strong
                              >-{{
                                formatCurrency(selectedPayslip.total_deductions)
                              }}</strong
                            >
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Net -->
            <div class="card mt-4 bg-primary text-white">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <h4 class="mb-0">Salaire Net à Payer</h4>
                  </div>
                  <div class="col-md-6 text-end">
                    <h2 class="mb-0">{{ formatCurrency(selectedPayslip.net_salary) }}</h2>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Fermer
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="downloadPayslip(selectedPayslip.payslip_id)"
            >
              <i class="fas fa-download"></i> Télécharger PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from "bootstrap";

export default {
  name: "Payroll",

  data() {
    return {
      payslips: [],
      employees: [],
      loading: false,
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: new Date().getFullYear(),
      filterEmployee: "",
      filterStatus: "",

      generateMode: "all",
      generateForm: {
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear(),
        employee_id: "",
      },

      calculationPreview: null,
      selectedPayslip: null,

      generateModal: null,
      detailModal: null,
    };
  },

  computed: {
    filteredPayslips() {
      return this.payslips.filter((p) => {
        if (this.filterEmployee && p.employee_id != this.filterEmployee) return false;
        if (this.filterStatus && p.status !== this.filterStatus) return false;
        return true;
      });
    },

    totalGross() {
      return this.payslips.reduce((sum, p) => sum + parseFloat(p.gross_salary || 0), 0);
    },

    totalNet() {
      return this.payslips.reduce((sum, p) => sum + parseFloat(p.net_salary || 0), 0);
    },
  },

  mounted() {
    this.fetchPayslips();
    this.fetchEmployees();
    this.generateModal = new Modal(document.getElementById("generateModal"));
    this.detailModal = new Modal(document.getElementById("detailModal"));
  },

  methods: {
    async fetchPayslips() {
      this.loading = true;
      try {
        const params = new URLSearchParams({
          month: this.selectedMonth,
          year: this.selectedYear,
        });
        const response = await axios.get(`/payroll/payslips?${params.toString()}`);
        this.payslips = response.data.data || response.data;
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du chargement");
      } finally {
        this.loading = false;
      }
    },

    async fetchEmployees() {
      try {
        const response = await axios.get("/employees");
        this.employees = response.data.data || response.data;
      } catch (error) {
        console.error("Erreur:", error);
      }
    },

    applyFilters() {
      // Les filtres sont appliqués via computed property
    },

    showGenerateModal() {
      this.generateForm.month = this.selectedMonth;
      this.generateForm.year = this.selectedYear;
      this.calculationPreview = null;
      this.generateModal.show();
    },

    async previewCalculation() {
      if (!this.generateForm.employee_id) return;

      try {
        const response = await axios.post("/payroll/calculate", {
          employee_id: this.generateForm.employee_id,
          month: parseInt(this.generateForm.month),
          year: parseInt(this.generateForm.year),
        });
        this.calculationPreview = response.data.data;
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du calcul");
      }
    },

    async generatePayslips() {
      try {
        let response;

        if (this.generateMode === "single") {
          if (!this.generateForm.employee_id) {
            alert("Veuillez sélectionner un employé");
            return;
          }

          response = await axios.post("/payroll/generate", {
            employee_id: this.generateForm.employee_id,
            month: parseInt(this.generateForm.month),
            year: parseInt(this.generateForm.year),
          });
        } else {
          response = await axios.post("/payroll/generate-bulk", {
            month: parseInt(this.generateForm.month),
            year: parseInt(this.generateForm.year),
          });
        }

        alert(response.data.message);
        this.generateModal.hide();
        this.fetchPayslips();
      } catch (error) {
        console.error("Erreur:", error);
        alert(error.response?.data?.message || "Erreur lors de la génération");
      }
    },

    async viewPayslip(id) {
      try {
        const response = await axios.get(`/payroll/payslips/${id}`);
        this.selectedPayslip = response.data.data;
        this.detailModal.show();
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du chargement");
      }
    },

    async finalizePayslip(id) {
      if (!confirm("Finaliser cette fiche de paie ?")) return;

      try {
        await axios.post(`/payroll/payslips/${id}/finalize`);
        alert("Fiche de paie finalisée");
        this.fetchPayslips();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    async markAsPaid(id) {
      if (!confirm("Marquer cette fiche comme payée ?")) return;

      try {
        await axios.post(`/payroll/payslips/${id}/mark-paid`);
        alert("Fiche marquée comme payée");
        this.fetchPayslips();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    async deletePayslip(id) {
      if (!confirm("Supprimer cette fiche de paie ?")) return;

      try {
        await axios.delete(`/payroll/payslips/${id}`);
        alert("Fiche supprimée");
        this.fetchPayslips();
      } catch (error) {
        alert(error.response?.data?.message || "Erreur");
      }
    },

    downloadPayslip(id) {
      // Télécharger directement
      window.open(`/api/payroll/payslips/${id}/download`, "_blank");
    },

    async downloadMonthlyZip() {
      if (
        !confirm(
          `Télécharger toutes les fiches de ${this.getMonthName(this.selectedMonth)} ${
            this.selectedYear
          } en ZIP ?`
        )
      ) {
        return;
      }

      try {
        const response = await axios.post(
          "/payroll/download-monthly-zip",
          {
            month: parseInt(this.selectedMonth),
            year: parseInt(this.selectedYear),
          },
          {
            responseType: "blob",
          }
        );

        // Créer un lien de téléchargement
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
          "download",
          `Fiches_Paie_${this.selectedMonth}_${this.selectedYear}.zip`
        );
        document.body.appendChild(link);
        link.click();
        link.remove();

        alert("Archive ZIP téléchargée avec succès");
      } catch (error) {
        console.error("Erreur:", error);
        alert("Erreur lors du téléchargement");
      }
    },

    formatCurrency(value) {
      return new Intl.NumberFormat("fr-FR").format(value) + " MGA";
    },

    getMonthName(month) {
      const months = [
        "",
        "Jan",
        "Fév",
        "Mar",
        "Avr",
        "Mai",
        "Juin",
        "Juil",
        "Aoû",
        "Sep",
        "Oct",
        "Nov",
        "Déc",
      ];
      return months[month];
    },

    getStatusClass(status) {
      const classes = {
        draft: "pending",
        finalized: "approved",
        paid: "approved",
      };
      return classes[status] || "pending";
    },

    getStatusLabel(status) {
      const labels = {
        draft: "Brouillon",
        finalized: "Finalisé",
        paid: "Payé",
      };
      return labels[status] || status;
    },
  },
};
</script>

<style scoped>
.payslip-header {
  padding: var(--space-6);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
}

.card-header.bg-success,
.card-header.bg-danger {
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
</style>
