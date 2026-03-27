<template>
  <div class="app-wrapper">
    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand bg-body">
      <!--begin::Container-->
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
              <!-- CORRECTION ICI -->
            </a>
          </li>

          <ul class="navbar-nav ms-auto"></ul>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <LanguageSwitcher />
          </li>
          <!--end::Navbar Search-->

          <!--begin::Fullscreen Toggle-->
          <li class="nav-item">
            <a class="nav-link" href="#" data-lte-toggle="fullscreen">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i
                data-lte-icon="minimize"
                class="bi bi-fullscreen-exit"
                style="display: none"
              ></i>
            </a>
          </li>
          <!--end::Fullscreen Toggle-->
          <!--begin::User Menu Dropdown-->
          <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <!-- <img
                src="./assets/img/user2-160x160.jpg"
                class="user-image rounded-circle shadow"
                alt="User Image"
              /> -->
              <i class="bi bi-person-circle"></i>

              <span class="d-none d-md-inline"> SuperAdmin</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image-->
              <li class="user-header text-bg-primary">
                <!-- <img
                  src="./assets/img/user2-160x160.jpg"
                  class="rounded-circle shadow"
                  alt="User Image"
                /> -->
                <p>
                  Alexander Pierce - Web Developer
                  <small>Member since Nov. 2023</small>
                </p>
              </li>
              <!--end::User Image-->
              <!--begin::Menu Body-->
              <li class="user-body">

              </li>
              <!--end::Menu Body-->
              <!--begin::Menu Footer-->
              <li class="nav-item">
                <a class="nav-link text-danger" @click="logout">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </a>
              </li>
              <!--end::Menu Footer-->
            </ul>
          </li>
          <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
      </div>
      <!--end::Container-->
    </nav>

    <!-- Sidebar -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="./index.html" class="brand-link">
          <!--begin::Brand Image-->
          <!-- <img
            src="./assets/img/AdminLTELogo.png"
            alt="AdminLTE Logo"
            class="brand-image opacity-75 shadow"
          /> -->
          <!--end::Brand Image-->
          <!--begin::Brand Text-->
          <span class="brand-text fw-light">PayFlex</span>
          <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
      </div>
      <!--end::Sidebar Brand-->
      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <!--begin::Sidebar Menu-->
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation"
          >

           <!-- Section Administration -->
            <li class="nav-header">ADMINISTRATION</li>

            <li class="nav-item">
              <router-link to="/admin/dashboard" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                <p>Dashboard</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/employees" class="nav-link">
                <i class="bi bi-people"></i>
                <p>Employees</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/payroll" class="nav-link">
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Payroll</p>
              </router-link>
            </li>



            <li class="nav-item">
              <router-link to="/admin/companies" class="nav-link">
                <i class="nav-icon fas fa-building"></i>
                <p>Companies</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/users" class="nav-link">
                <i class="nav-icon fas fa-user-cog"></i>
                <p>Users</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/roles" class="nav-link">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>Roles</p>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/admin/attendance" class="nav-link">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>Attendance</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/leaves" class="nav-link">
                <i class="nav-icon fas fa-calendar-times"></i>
                <p>Leaves</p>
              </router-link>
            </li>



            <li class="nav-item">
              <router-link to="/admin/positions" class="nav-link">
                <i class="bi bi-pc-display-horizontal"></i>
                <p>Positions</p>
              </router-link>
            </li>

            <li class="nav-item">
              <router-link to="/admin/settings" class="nav-link">
                <i class="bi bi-gear"></i>
                <p>Settings</p>
              </router-link>
            </li>
          </ul>
          <!--end::Sidebar Menu-->
        </nav>
      </div>
      <!--end::Sidebar Wrapper-->
    </aside>

    <!-- Main content -->
    <main class="app-main">
      <router-view />
    </main>
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
