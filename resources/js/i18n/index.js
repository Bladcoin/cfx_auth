import { createI18n } from 'vue-i18n'
import ru from './ru.js'
import en from './en.js'

let locale = localStorage.getItem('locale')

if (!['ru', 'en'].includes(locale)) {
	locale = 'ru'
}

export default createI18n({
	locale: locale,
	fallbackLocale: locale,
	messages: {
		ru,
		en,
	}
})
