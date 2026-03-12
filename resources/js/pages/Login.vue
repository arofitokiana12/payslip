<template>
  <div class="login-page">
    <div class="login-bg">
      <div class="login-shape login-shape-1"></div>
      <div class="login-shape login-shape-2"></div>
      <div class="login-shape login-shape-3"></div>
    </div>
    <div class="login-container">
      <div class="login-card">
        <div class="login-card-inner">
          <div class="login-brand">
            <span class="login-logo">PayFlex</span>
            <p class="login-tagline">{{ $t('auth.tagline') }}</p>
          </div>
          <h1 class="login-title">{{ $t('auth.welcome') }}</h1>
          <p class="login-subtitle">{{ $t('auth.sign_in_account') }}</p>

          <form @submit.prevent="loginUser" class="login-form">
            <div class="form-group-premium">
              <label class="form-label-premium">{{ $t('auth.email') }}</label>
              <div class="input-wrap">
                <i class="bi bi-envelope input-icon-premium"></i>
                <input
                  v-model="email"
                  type="email"
                  class="input-premium"
                  :placeholder="$t('auth.email_placeholder')"
                  required
                  autocomplete="email"
                />
              </div>
            </div>
            <div class="form-group-premium">
              <label class="form-label-premium">{{ $t('auth.password') }}</label>
              <div class="input-wrap">
                <i class="bi bi-lock input-icon-premium"></i>
                <input
                  v-model="password"
                  type="password"
                  class="input-premium"
                  :placeholder="$t('auth.password_placeholder')"
                  required
                  autocomplete="current-password"
                />
              </div>
            </div>
            <button type="submit" class="btn-login" :disabled="loading">
              <span v-if="!loading">{{ $t('auth.connect') }}</span>
              <span v-else class="d-flex align-items-center justify-content-center gap-2">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                {{ $t('auth.connecting') }}
              </span>
            </button>
          </form>

          <p v-if="error" class="login-error">
            <i class="bi bi-exclamation-circle me-2"></i>{{ error }}
          </p>
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
const loading = ref(false)
const router = useRouter()

const loginUser = async () => {
  error.value = ''
  loading.value = true
  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
    })
    if (response.data?.token) {
      localStorage.setItem('token', response.data.token)
    }
    router.push({ name: 'Dashboard' })
  } catch (err) {
    if (err.response?.status === 401) {
      error.value = 'Email ou mot de passe incorrect.'
    } else {
      error.value = 'Une erreur est survenue. Réessayez.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.login-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%);
}

.login-shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.4;
}

.login-shape-1 {
  width: 400px;
  height: 400px;
  background: #6366f1;
  top: -100px;
  right: -100px;
}

.login-shape-2 {
  width: 300px;
  height: 300px;
  background: #8b5cf6;
  bottom: 20%;
  left: -80px;
}

.login-shape-3 {
  width: 200px;
  height: 200px;
  background: #a78bfa;
  bottom: -50px;
  right: 20%;
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
  padding: 24px;
}

.login-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.login-card-inner {
  padding: 40px 36px;
}

.login-brand {
  text-align: center;
  margin-bottom: 28px;
}

.login-logo {
  display: block;
  font-size: 1.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.5px;
}

.login-tagline {
  font-size: 0.8125rem;
  color: #64748b;
  margin: 4px 0 0;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 4px;
  text-align: center;
}

.login-subtitle {
  font-size: 0.9375rem;
  color: #64748b;
  margin: 0 0 28px;
  text-align: center;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group-premium {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label-premium {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #374151;
}

.input-wrap {
  position: relative;
}

.input-icon-premium {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.1rem;
  pointer-events: none;
}

.input-premium {
  width: 100%;
  height: 48px;
  padding: 0 16px 0 44px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.9375rem;
  color: #0f172a;
  background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-premium::placeholder {
  color: #94a3b8;
}

.input-premium:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.btn-login {
  height: 48px;
  margin-top: 8px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.45);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
}

.btn-login:disabled {
  opacity: 0.8;
  cursor: not-allowed;
}

.login-error {
  margin: 20px 0 0;
  padding: 12px 16px;
  background: #fef2f2;
  color: #dc2626;
  border-radius: 12px;
  font-size: 0.875rem;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
