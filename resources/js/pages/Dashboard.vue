<!-- resources/js/pages/Dashboard.vue -->
<template>
  <div class="container-fluid mt-4 dashboard-page">
    <!-- Header -->
    <div class="dashboard-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h2 class="dashboard-title">
            <i class="fas fa-chart-line"></i>
            {{ $t('dashboard.title') }}
          </h2>
          <p class="text-muted">{{ $t('dashboard.overview') }}</p>
        </div>
        <div class="col-md-6 text-end">
          <select class="form-select d-inline-block w-auto me-2" v-model="selectedMonth" @change="fetchDashboard">
            <option v-for="m in 12" :key="m" :value="m">{{ $t('payroll.month_' + m) }}</option>
          </select>
          <select class="form-select d-inline-block w-auto" v-model="selectedYear" @change="fetchDashboard">
            <option value="2024">2024</option>
            <option value="2025">2025</option>
            <option value="2026">2026</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Alertes -->
    <div v-if="alerts.length > 0" class="row mb-4">
      <div class="col-12">
        <div v-for="alert in alerts" :key="alert.title" :class="'alert alert-' + alert.type" class="d-flex align-items-center">
          <i :class="'fas ' + alert.icon + ' me-3'" style="font-size: 1.5rem;"></i>
          <div class="flex-grow-1">
            <strong>{{ alert.title }}</strong>
            <p class="mb-0">{{ alert.message }}</p>
          </div>
          <router-link v-if="alert.action" :to="alert.action" class="btn btn-sm btn-outline-primary">
            {{ $t('dashboard.see') }}
          </router-link>
        </div>
      </div>
    </div>

    <!-- Stats Cards Row 1 - Employés & Paie -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">{{ $t('dashboard.active_employees') }}</div>
            <div class="stat-value">{{ stats.employees?.active || 0 }}</div>
            <div class="stat-footer">
              <span class="badge bg-success">
                +{{ stats.employees?.new_this_month || 0 }} {{ $t('dashboard.this_month') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <i class="fas fa-money-bill-wave"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">{{ $t('dashboard.payroll_mass') }}</div>
            <div class="stat-value">{{ formatCurrency(stats.payroll?.total_net || 0) }}</div>
            <div class="stat-footer">
              <span :class="stats.payroll?.variation_percent >= 0 ? 'text-success' : 'text-danger'">
                <i :class="stats.payroll?.variation_percent >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ Math.abs(stats.payroll?.variation_percent || 0) }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">{{ $t('dashboard.attendance_rate') }}</div>
            <div class="stat-value">{{ stats.attendance?.attendance_rate || 0 }}%</div>
            <div class="stat-footer">
              <span class="text-muted">
                {{ stats.attendance?.present || 0 }} / {{ stats.attendance?.total_records || 0 }} {{ $t('dashboard.days') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <i class="fas fa-calendar-times"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">{{ $t('dashboard.leaves_pending') }}</div>
            <div class="stat-value">{{ stats.leaves?.pending || 0 }}</div>
            <div class="stat-footer">
              <span class="badge bg-warning">
                {{ stats.leaves?.total_days || 0 }} {{ $t('dashboard.days_total') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
      <!-- Tendance Paie -->
      <div class="col-xl-8 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-line text-primary"></i>
              {{ $t('dashboard.payroll_evolution') }}
            </h5>
          </div>
          <div class="card-body">
            <canvas ref="payrollChart" height="80"></canvas>
          </div>
        </div>
      </div>

      <!-- Distribution Présences -->
      <div class="col-xl-4 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-pie text-success"></i>
              {{ $t('dashboard.attendance_month') }}
            </h5>
          </div>
          <div class="card-body">
            <canvas ref="attendanceChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
      <!-- Distribution Congés -->
      <div class="col-xl-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-bar text-warning"></i>
              {{ $t('dashboard.leave_distribution') }}
            </h5>
          </div>
          <div class="card-body">
            <canvas ref="leaveChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Employés par Poste -->
      <div class="col-xl-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-briefcase text-info"></i>
              {{ $t('dashboard.employees_by_position') }}
            </h5>
          </div>
          <div class="card-body">
            <canvas ref="positionChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Activités Récentes & Stats -->
    <div class="row">
      <!-- Activités Récentes -->
      <div class="col-xl-8 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-history text-primary"></i>
              {{ $t('dashboard.recent_activities') }}
            </h5>
          </div>
          <div class="card-body">
            <div class="activity-timeline">
              <div
                v-for="activity in recentActivities"
                :key="activity.title + activity.date"
                class="activity-item"
              >
                <div class="activity-icon" :class="'bg-' + activity.color">
                  <i :class="'fas ' + activity.icon"></i>
                </div>
                <div class="activity-content">
                  <div class="activity-title">{{ activity.title }}</div>
                  <div class="activity-description">{{ activity.description }}</div>
                  <div class="activity-time">{{ activity.relative_time }}</div>
                </div>
              </div>

              <div v-if="recentActivities.length === 0" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                {{ $t('dashboard.no_activity') }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="col-xl-4 mb-4">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-clipboard-list text-success"></i>
              {{ $t('dashboard.payslip_status') }}
            </h5>
          </div>
          <div class="card-body">
            <div class="quick-stat-item">
              <div class="quick-stat-label">{{ $t('payroll.drafts') }}</div>
              <div class="quick-stat-value text-warning">
                {{ stats.payroll?.by_status?.draft || 0 }}
              </div>
            </div>
            <div class="quick-stat-item">
              <div class="quick-stat-label">{{ $t('payroll.finalized') }}</div>
              <div class="quick-stat-value text-info">
                {{ stats.payroll?.by_status?.finalized || 0 }}
              </div>
            </div>
            <div class="quick-stat-item">
              <div class="quick-stat-label">Payées</div>
              <div class="quick-stat-value text-success">
                {{ stats.payroll?.by_status?.paid || 0 }}
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-pie text-warning"></i>
              {{ $t('dashboard.leave_types') }}
            </h5>
          </div>
          <div class="card-body">
            <div class="quick-stat-item" v-if="stats.leaves?.by_type">
              <div class="quick-stat-label">{{ $t('dashboard.annual') }}</div>
              <div class="quick-stat-value">{{ stats.leaves.by_type.annual || 0 }}</div>
            </div>
            <div class="quick-stat-item" v-if="stats.leaves?.by_type">
              <div class="quick-stat-label">{{ $t('dashboard.sick') }}</div>
              <div class="quick-stat-value">{{ stats.leaves.by_type.sick || 0 }}</div>
            </div>
            <div class="quick-stat-item" v-if="stats.leaves?.by_type">
              <div class="quick-stat-label">{{ $t('dashboard.maternity') }}</div>
              <div class="quick-stat-value">{{ stats.leaves.by_type.maternity || 0 }}</div>
            </div>
            <div class="quick-stat-item" v-if="stats.leaves?.by_type">
              <div class="quick-stat-label">{{ $t('dashboard.unpaid') }}</div>
              <div class="quick-stat-value">{{ stats.leaves.by_type.unpaid || 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'Dashboard',

  data() {
    return {
      stats: {
        employees: {},
        payroll: {},
        attendance: {},
        leaves: {}
      },
      charts: {},
      recentActivities: [],
      alerts: [],
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: new Date().getFullYear(),
      chartInstances: {}
    };
  },

  mounted() {
    this.fetchDashboard();
    this.fetchAlerts();
  },

  beforeUnmount() {
    // Détruire les graphiques
    Object.values(this.chartInstances).forEach(chart => {
      if (chart) chart.destroy();
    });
  },

  methods: {
    async fetchDashboard() {
      try {
        const params = new URLSearchParams({
          month: this.selectedMonth,
          year: this.selectedYear
        });

        const response = await axios.get(`/dashboard?${params.toString()}`);
        const data = response.data;

        this.stats = data?.stats || {};
        this.charts = data?.charts || {};
        this.recentActivities = data?.recent_activities || [];

        // Créer les graphiques seulement si on a des données et des refs valides
        this.$nextTick(() => {
          this.createPayrollChart();
          this.createAttendanceChart();
          this.createLeaveChart();
          this.createPositionChart();
        });
      } catch (error) {
        console.error('Erreur:', error);
      }
    },

    async fetchAlerts() {
      try {
        const response = await axios.get('/dashboard/alerts');
        const data = response.data?.data ?? response.data;
        this.alerts = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Erreur:', error);
        this.alerts = [];
      }
    },

    createPayrollChart() {
      if (!this.$refs.payrollChart || !this.charts?.payroll_trend) {
        return;
      }

      if (this.chartInstances.payroll) {
        this.chartInstances.payroll.destroy();
      }

      const ctx = this.$refs.payrollChart.getContext('2d');
      if (!ctx) return;

      this.chartInstances.payroll = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.charts.payroll_trend?.labels || [],
          datasets: this.charts.payroll_trend?.datasets.map(dataset => ({
            label: dataset.label,
            data: dataset.data,
            borderColor: dataset.color,
            backgroundColor: dataset.color + '20',
            tension: 0.4,
            fill: true
          })) || []
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    },

    createAttendanceChart() {
      if (!this.$refs.attendanceChart || !this.charts?.attendance_overview) {
        return;
      }

      if (this.chartInstances.attendance) {
        this.chartInstances.attendance.destroy();
      }

      const chartData = this.charts.attendance_overview;

      // Palette colorée locale pour le donut de présence
      const basePalette = {
        present: '#22c55e',
        late: '#f97316',
        absent: '#ef4444',
        'half-day': '#06b6d4',
        'half day': '#06b6d4',
        remote: '#8b5cf6'
      };

      const labels = chartData?.labels || [];
      const data = chartData?.data || [];

      const colors = labels.map(label => {
        const raw = String(label);
        const key = raw.toLowerCase();
        if (basePalette[key]) {
          return basePalette[key];
        }
        if (chartData.colors && chartData.colors[raw]) {
          return chartData.colors[raw];
        }
        return '#e5e7eb';
      });

      const ctx = this.$refs.attendanceChart.getContext('2d');
      if (!ctx) return;

      this.chartInstances.attendance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor: colors,
            borderWidth: 0,
            hoverOffset: 6,
            borderColor: '#ffffff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                boxWidth: 10
              }
            }
          },
          cutout: '68%'
        }
      });
    },

    createLeaveChart() {
      if (!this.$refs.leaveChart || !this.charts?.leave_distribution) {
        return;
      }

      if (this.chartInstances.leave) {
        this.chartInstances.leave.destroy();
      }

      const ctx = this.$refs.leaveChart.getContext('2d');
      if (!ctx) return;

      this.chartInstances.leave = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.charts.leave_distribution?.labels || [],
          datasets: [{
            label: 'Nombre de congés',
            data: this.charts.leave_distribution?.data || [],
            backgroundColor: this.charts.leave_distribution?.colors || []
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    },

    createPositionChart() {
      if (!this.$refs.positionChart || !this.charts?.employee_by_position) {
        return;
      }

      if (this.chartInstances.position) {
        this.chartInstances.position.destroy();
      }

      const ctx = this.$refs.positionChart.getContext('2d');
      if (!ctx) return;

      this.chartInstances.position = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.charts.employee_by_position?.labels || [],
          datasets: [{
            label: 'Nombre d\'employés',
            data: this.charts.employee_by_position?.data || [],
            backgroundColor: '#667eea'
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0
      }).format(value) + ' MGA';
    }
  }
};
</script>

<style scoped>
.dashboard-header {
  padding: var(--space-6) 0;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: var(--space-2);
}

/* Stat Cards */
.stat-card {
  background: var(--bg-card);
  border-radius: var(--radius-2xl);
  padding: var(--space-6);
  box-shadow: var(--shadow-sm);
  display: flex;
  gap: var(--space-4);
  align-items: flex-start;
  transition: all var(--transition-base);
  border: 1px solid var(--border-light);
}

.stat-card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-4px);
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  flex-shrink: 0;
  box-shadow: var(--shadow-md);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  font-weight: 600;
  margin-bottom: var(--space-2);
}

.stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1;
  margin-bottom: var(--space-3);
}

.stat-footer {
  font-size: 0.8125rem;
}

/* Activity Timeline */
.activity-timeline {
  max-height: 500px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  gap: var(--space-4);
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  transition: background var(--transition-fast);
}

.activity-item:hover {
  background: var(--bg-hover);
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: var(--space-1);
}

.activity-description {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: var(--space-1);
}

.activity-time {
  font-size: 0.75rem;
  color: var(--text-tertiary);
}

/* Quick Stats */
.quick-stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-3) 0;
  border-bottom: 1px solid var(--border-light);
}

.quick-stat-item:last-child {
  border-bottom: none;
}

.quick-stat-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.quick-stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
}
</style>
