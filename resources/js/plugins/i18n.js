import { createI18n } from 'vue-i18n';
import fr from '../locales/fr.json';
import en from '../locales/en.json';

// Récupérer la langue sauvegardée ou utiliser le français par défaut
const savedLocale = localStorage.getItem('locale') || 'en';

const i18n = createI18n({
  legacy: false, // Pour utiliser Composition API
  locale: savedLocale,
  fallbackLocale: 'fr',
  messages: {
    fr,
    en

  },
  // Options supplémentaires
  globalInjection: true,
  missingWarn: false,
  fallbackWarn: false
});

export default i18n;
