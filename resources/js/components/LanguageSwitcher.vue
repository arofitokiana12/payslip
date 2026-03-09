<template>
  <div class="language-switcher">
    <select
      class="form-select form-select-sm"
      v-model="currentLocale"
      @change="changeLocale"
    >
      <option value="fr">🇫🇷 Français</option>
      <option value="en">🇬🇧 English</option>
      <option value="mg">🇲🇬 Malagasy</option>
    </select>
  </div>
</template>

<script>
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';

export default {
  name: 'LanguageSwitcher',
  setup() {
    const { locale } = useI18n();
    const currentLocale = ref(locale.value);

    const changeLocale = () => {
      locale.value = currentLocale.value;
      localStorage.setItem('locale', currentLocale.value);

      // Optionnel: recharger la page pour appliquer partout
      // window.location.reload();
    };

    return {
      currentLocale,
      changeLocale
    };
  }
};
</script>

<style scoped>
.language-switcher {
  display: inline-block;
}

.form-select-sm {
  width: auto;
  padding: 0.25rem 2rem 0.25rem 0.5rem;
}
</style>
