// Drag and Drop functionality for slides reordering
document.addEventListener('DOMContentLoaded', function() {
    const slidesGrid = document.getElementById('slides-grid');
    if (!slidesGrid) return;

    const slideItems = slidesGrid.querySelectorAll('.slide-item');
    let draggedElement = null;

    slideItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
    });

    function handleDragStart(e) {
        draggedElement = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';

        const targetElement = e.target.closest('.slide-item');
        if (targetElement && targetElement !== draggedElement) {
            const rect = targetElement.getBoundingClientRect();
            const midpoint = rect.left + rect.width / 2;
            
            if (e.clientX < midpoint) {
                targetElement.parentNode.insertBefore(draggedElement, targetElement);
            } else {
                targetElement.parentNode.insertBefore(draggedElement, targetElement.nextSibling);
            }
        }

        return false;
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        return false;
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        
        // Update slide numbers
        updateSlideNumbers();
        
        // Send new order to server
        saveOrder();
    }

    function updateSlideNumbers() {
        const slides = slidesGrid.querySelectorAll('.slide-item');
        slides.forEach((slide, index) => {
            const numberSpan = slide.querySelector('.slide-number');
            if (numberSpan) {
                numberSpan.textContent = '#' + (index + 1);
            }
        });
    }

    function saveOrder() {
        const slideshowId = slidesGrid.dataset.slideshowId;
        const slides = slidesGrid.querySelectorAll('.slide-item');
        const order = [];

        slides.forEach((slide, index) => {
            order.push(parseInt(slide.dataset.slideId));
        });

        fetch(`/slide/reorder/${slideshowId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Order saved successfully');
            }
        })
        .catch(error => {
            console.error('Error saving order:', error);
        });
    }

    // File input preview
    const fileInput = document.getElementById('images');
    const selectedFilesDiv = document.getElementById('selected-files');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            const label = document.getElementById('upload-label');
            const textDiv = label.querySelector('.upload-text');
            
            if (files.length > 0) {
                // Update label text
                if (textDiv) {
                    textDiv.innerHTML = `<strong style="color: #c8433b;">Wybrano ${files.length} ${files.length === 1 ? 'plik' : 'plików'}</strong>`;
                }
                label.style.borderColor = '#c8433b';
                label.style.backgroundColor = '#f5f5f5';
                
                // Show file list
                if (selectedFilesDiv) {
                    selectedFilesDiv.style.display = 'block';
                    selectedFilesDiv.innerHTML = '<h4 style="margin: 1rem 0 0.5rem 0; color: #333;">Wybrane pliki:</h4><ul style="margin: 0; padding-left: 1.5rem;">';
                    
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const fileSize = (file.size / 1024 / 1024).toFixed(2);
                        selectedFilesDiv.innerHTML += `<li style="margin: 0.25rem 0; color: #666;">${file.name} <span style="color: #999;">(${fileSize} MB)</span></li>`;
                    }
                    
                    selectedFilesDiv.innerHTML += '</ul>';
                }
            } else {
                // Reset if no files
                if (textDiv) {
                    textDiv.textContent = 'Kliknij lub przeciągnij zdjęcia tutaj';
                }
                label.style.borderColor = '#ddd';
                label.style.backgroundColor = 'transparent';
                if (selectedFilesDiv) {
                    selectedFilesDiv.style.display = 'none';
                }
            }
        });
    }
});
