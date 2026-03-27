<template>
  <div class="app-wrapper">
    <!-- Navbar premium -->
    <nav class="app-header navbar navbar-expand">
      <div class="container-fluid px-4">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
              <i class="bi bi-list fs-4"></i>
            </a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <li class="nav-item">
            <LanguageSwitcher />
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-2 px-3 rounded-3" data-bs-toggle="dropdown" id="userDropdown">
              <span class="user-avatar-sm">
                <i class="bi bi-person-fill"></i>
              </span>
              <span class="d-none d-md-inline text-dark fw-semibold">SuperAdmin</span>
              <i class="bi bi-chevron-down small"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="min-width: 220px; border-radius: 12px;">
              <li class="px-3 py-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <span class="user-avatar-sm"><i class="bi bi-person-fill"></i></span>
                  <div>
                    <div class="fw-semibold">Super Admin</div>
                    <small class="text-muted">{{ $t('layout.admin_account') }}</small>
                  </div>
                </div>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" @click.prevent="logout">
                  <i class="bi bi-box-arrow-right text-danger"></i>
                  <span>{{ $t('nav.logout') }}</span>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Corps: sidebar + contenu côte à côte -->
    <div class="app-body">
      <aside class="app-sidebar shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <router-link to="/admin/dashboard" class="brand-link text-decoration-none">
            <span class="brand-text">PayFlex</span>
          </router-link>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" role="navigation" id="navigation">
              <li class="nav-item">
                <router-link to="/admin/dashboard" class="nav-link">
                  <i class="bi bi-speedometer2"></i>
                  <p>{{ $t('nav.dashboard') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/employees" class="nav-link">
                  <i class="bi bi-people"></i>
                  <p>{{ $t('nav.employees') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/payroll" class="nav-link">
                  <i class="bi bi-wallet2"></i>
                  <p>{{ $t('nav.payroll') }}</p>
                </router-link>
              </li>
              <li class="nav-header">{{ $t('nav.administration') }}</li>
              <li class="nav-item">
                <router-link to="/admin/companies" class="nav-link">
                  <i class="bi bi-building"></i>
                  <p>{{ $t('nav.companies') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/users" class="nav-link">
                  <i class="bi bi-person-gear"></i>
                  <p>{{ $t('nav.users') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/roles" class="nav-link">
                  <i class="bi bi-shield-lock"></i>
                  <p>{{ $t('nav.roles') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/attendance" class="nav-link">
                  <i class="bi bi-calendar-check"></i>
                  <p>{{ $t('nav.attendance') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/leaves" class="nav-link">
                  <i class="bi bi-calendar-x"></i>
                  <p>{{ $t('nav.leaves') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/positions" class="nav-link">
                  <i class="bi bi-briefcase"></i>
                  <p>{{ $t('nav.positions') }}</p>
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/settings" class="nav-link">
                  <i class="bi bi-gear"></i>
                  <p>{{ $t('nav.settings') }}</p>
                </router-link>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <main class="app-main content-premium">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
const Default = {
  scrollbarTheme: "os-theme-light",
  scrollbarAutoHide: "leave",
  scrollbarClickScroll: true,
};
import LanguageSwitcher from "../components/LanguageSwitcher.vue";
import axios from "axios";

export default {
  name: "AdminLayout",
  components: {
    LanguageSwitcher,
  },

  methods: {
    async logout() {
      try {
        console.log("Logout clicked");

        // Si baseURL = '/api', alors '/logout' devient '/api/logout' ✅
        await axios.post("/logout");

        localStorage.removeItem("token");
        this.$router.push("/");
      } catch (e) {
        console.error("Logout error", e);
      }
    },
  },
};
</script>
