<template>
  <div class="login-page">
    <div class="login-card shadow-lg">
      <div class="row g-0 h-100">

        <!-- Left side -->
        <div class="col-md-6 left-panel d-flex align-items-center justify-content-center">
          <div class="logo-box">
            <img src="/public/logo.png" alt="PayFlex" class="logo" />

          </div>
        </div>

        <!-- Right side -->
        <div class="col-md-6 right-panel d-flex flex-column justify-content-center px-5">
          <h2 class="text-center mb-2">Welcome back</h2>
          <p class="text-center text-muted mb-4">Please sign in to your account</p>

          <form @submit.prevent="loginUser">
            <div class="mb-3 position-relative">
              <i class="bi bi-person input-icon"></i>
              <input
                v-model="email"
                type="email"
                class="form-control ps-5"
                placeholder="Username"
                required
              />
            </div>

            <div class="mb-3 position-relative">
              <i class="bi bi-lock input-icon"></i>
              <input
                v-model="password"
                type="password"
                class="form-control ps-5"
                placeholder="Password"
                required
              />
            </div>

            <button type="submit" class="btn btn-dark w-100 py-2">
              Sign in
            </button>
          </form>

          <p v-if="error" class="text-danger text-center mt-3">{{ error }}</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()  // ✅ Pour la redirection

const loginUser = async () => {
  error.value = ''
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value,
    })

    // Stocker le token si nécessaire
    localStorage.setItem('token', response.data.token)

    // Redirection vers le dashboard
    router.push({ name: 'Dashboard' })

  } catch (err) {
    if (err.response && err.response.status === 401) {
      error.value = 'Invalid email or password'
    } else {
      error.value = 'Something went wrong'
      console.error(err)
    }
  }
}
</script>

