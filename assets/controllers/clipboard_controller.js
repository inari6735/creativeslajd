import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['source', 'button']

    copy(event) {
        event.preventDefault();
        
        const text = this.sourceTarget.value;
        
        // Select the text
        this.sourceTarget.select();
        this.sourceTarget.setSelectionRange(0, 99999); // For mobile devices
        
        // Copy to clipboard
        navigator.clipboard.writeText(text).then(() => {
            // Success feedback
            const originalHTML = this.buttonTarget.innerHTML;
            this.buttonTarget.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Skopiowano!';
            this.buttonTarget.classList.add('btn-success');
            this.buttonTarget.classList.remove('btn-secondary');
            
            // Reset after 2 seconds
            setTimeout(() => {
                this.buttonTarget.innerHTML = originalHTML;
                this.buttonTarget.classList.remove('btn-success');
                this.buttonTarget.classList.add('btn-secondary');
            }, 2000);
        }).catch(err => {
            console.error('Błąd kopiowania:', err);
            alert('Nie udało się skopiować linku');
        });
    }
}
