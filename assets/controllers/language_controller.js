import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dropdown']

    connect() {
        // Close dropdown when clicking outside
        document.addEventListener('click', this.closeDropdown.bind(this));
    }

    disconnect() {
        document.removeEventListener('click', this.closeDropdown.bind(this));
    }

    toggle(event) {
        event.stopPropagation();
        this.dropdownTarget.classList.toggle('show');
    }

    closeDropdown(event) {
        if (!this.element.contains(event.target)) {
            this.dropdownTarget.classList.remove('show');
        }
    }

    switchLanguage(event) {
        event.preventDefault();
        const locale = event.currentTarget.dataset.locale;
        
        // Set cookie for locale
        document.cookie = `LOCALE=${locale}; path=/; max-age=31536000`; // 1 year
        
        // Reload page
        window.location.reload();
    }
}
