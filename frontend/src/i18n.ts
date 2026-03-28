import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ru from './locales/ru.json';
import uk from './locales/uk.json';

const savedLocale = localStorage.getItem('locale') || 'en';

export const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: { en, ru, uk },
});

export function setLocale(locale: string) {
  (i18n.global.locale as any).value = locale;
  localStorage.setItem('locale', locale);
}
