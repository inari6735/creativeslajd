import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'label', 'labelText', 'filesList']
    
    connect() {
        console.log('File upload controller connected');
        this.setupDragAndDrop();
    }

    setupDragAndDrop() {
        // Prevent default drag behaviors
        ['dragenter', 'dragover'].forEach(eventName => {
            document.body.addEventListener(eventName, (e) => {
                if (!e.target.closest('.slide-item') && !e.target.closest('#slides-grid')) {
                    e.preventDefault();
                }
            }, false);
        });
        
        document.body.addEventListener('drop', (e) => {
            if (!e.target.closest('#upload-label')) {
                e.preventDefault();
            }
        }, false);
    }

    dragEnter(event) {
        event.preventDefault();
        this.labelTarget.style.borderColor = '#c32027';
        this.labelTarget.style.backgroundColor = '#ffe5e5';
    }

    dragOver(event) {
        event.preventDefault();
        this.labelTarget.style.borderColor = '#c32027';
        this.labelTarget.style.backgroundColor = '#ffe5e5';
    }

    dragLeave(event) {
        this.labelTarget.style.borderColor = '';
        this.labelTarget.style.backgroundColor = '';
    }

    drop(event) {
        event.preventDefault();
        this.dragLeave();
        
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            this.inputTarget.files = files;
            this.updateFileList(files);
            console.log('Files dropped:', files.length);
        }
    }

    change(event) {
        console.log('File input changed, files:', event.target.files.length);
        this.updateFileList(event.target.files);
    }

    updateFileList(files) {
        if (files && files.length > 0) {
            // Update label text
            if (this.hasLabelTextTarget) {
                this.labelTextTarget.innerHTML = `<strong style="color: #c32027;">Wybrano ${files.length} ${files.length === 1 ? 'plik' : 'plików'}</strong>`;
            }
            this.labelTarget.style.borderColor = '#c32027';
            this.labelTarget.style.backgroundColor = '#f5f5f5';
            
            // Show file list
            if (this.hasFilesListTarget) {
                this.filesListTarget.style.display = 'block';
                let html = '<h4 style="margin: 1rem 0 0.5rem 0; color: #333;">Wybrane pliki:</h4><ul style="margin: 0; padding-left: 1.5rem;">';
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    html += `<li style="margin: 0.25rem 0; color: #666;">${file.name} <span style="color: #999;">(${fileSize} MB)</span></li>`;
                }
                
                html += '</ul>';
                this.filesListTarget.innerHTML = html;
            }
        } else {
            // Reset if no files
            if (this.hasLabelTextTarget) {
                this.labelTextTarget.textContent = 'Kliknij lub przeciągnij zdjęcia tutaj';
            }
            this.labelTarget.style.borderColor = '';
            this.labelTarget.style.backgroundColor = '';
            if (this.hasFilesListTarget) {
                this.filesListTarget.style.display = 'none';
            }
        }
    }
}
