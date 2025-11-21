// Drag and Drop functionality for slides reordering
document.addEventListener('DOMContentLoaded', function() {
    const slidesGrid = document.getElementById('slides-grid');
    if (!slidesGrid) return;

    let draggedElement = null;
    let draggedIndex = null;

    console.log('Drag & drop initialized');

    // Use event delegation for better performance and dynamic elements
    slidesGrid.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('slide-item')) {
            draggedElement = e.target;
            draggedElement.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', e.target.innerHTML);
            console.log('Drag started:', draggedElement.dataset.slideId);
        }
    });

    slidesGrid.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        // Remove all drag-over classes first
        slidesGrid.querySelectorAll('.slide-item').forEach(item => {
            item.classList.remove('drag-over');
        });

        const targetElement = e.target.closest('.slide-item');
        if (targetElement && targetElement !== draggedElement && draggedElement) {
            targetElement.classList.add('drag-over');
            
            const allSlides = Array.from(slidesGrid.querySelectorAll('.slide-item'));
            const draggedIndex = allSlides.indexOf(draggedElement);
            const targetIndex = allSlides.indexOf(targetElement);
            
            if (draggedIndex < targetIndex) {
                targetElement.parentNode.insertBefore(draggedElement, targetElement.nextSibling);
            } else {
                targetElement.parentNode.insertBefore(draggedElement, targetElement);
            }
        }
    });

    slidesGrid.addEventListener('drop', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        // Remove drag-over classes
        slidesGrid.querySelectorAll('.slide-item').forEach(item => {
            item.classList.remove('drag-over');
        });
        
        console.log('Drop completed');
    });

    slidesGrid.addEventListener('dragend', function(e) {
        if (draggedElement) {
            draggedElement.classList.remove('dragging');
            console.log('Drag ended');
            
            // Update slide numbers
            updateSlideNumbers();
            
            // Send new order to server
            saveOrder();
            
            draggedElement = null;
        }
    });

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

    // File input preview and drag & drop upload
    const fileInput = document.getElementById('images');
    const selectedFilesDiv = document.getElementById('selected-files');
    const uploadLabel = document.getElementById('upload-label');
    const uploadForm = document.querySelector('.upload-form');
    
    console.log('File upload initialized');
    console.log('File input:', fileInput);
    console.log('Upload label:', uploadLabel);
    console.log('Selected files div:', selectedFilesDiv);
    
    // Prevent default drag behaviors ONLY for file drops outside upload area
    // This prevents accidental file opening in browser
    ['dragenter', 'dragover'].forEach(eventName => {
        document.body.addEventListener(eventName, function(e) {
            // Don't prevent if it's a slide being dragged
            if (!e.target.closest('.slide-item') && !e.target.closest('#slides-grid')) {
                e.preventDefault();
            }
        }, false);
    });
    
    // Prevent file opening when dropped outside upload area
    document.body.addEventListener('drop', function(e) {
        if (!e.target.closest('#upload-label')) {
            e.preventDefault();
        }
    }, false);
    
    // Highlight upload area when dragging files over it
    if (uploadLabel) {
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                uploadLabel.style.borderColor = '#c8433b';
                uploadLabel.style.backgroundColor = '#ffe5e5';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                uploadLabel.style.borderColor = '#ddd';
                uploadLabel.style.backgroundColor = 'transparent';
            }, false);
        });

        // Handle dropped files
        uploadLabel.addEventListener('drop', handleFileDrop, false);
    }

    function handleFileDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            // Set files to input element
            fileInput.files = files;
            
            // Trigger change event to show file list
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
            console.log('Files dropped:', files.length);
        }
    }
    
    function updateFileList(files) {
        const label = document.getElementById('upload-label');
        const textDiv = label ? label.querySelector('.upload-text') : null;
        
        if (files && files.length > 0) {
            // Update label text
            if (textDiv) {
                textDiv.innerHTML = `<strong style="color: #c8433b;">Wybrano ${files.length} ${files.length === 1 ? 'plik' : 'plików'}</strong>`;
            }
            if (label) {
                label.style.borderColor = '#c8433b';
                label.style.backgroundColor = '#f5f5f5';
            }
            
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
            if (label) {
                label.style.borderColor = '#ddd';
                label.style.backgroundColor = 'transparent';
            }
            if (selectedFilesDiv) {
                selectedFilesDiv.style.display = 'none';
            }
        }
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            console.log('File input changed, files:', e.target.files.length);
            updateFileList(e.target.files);
        });
        
        // Check on page load if there are any files (shouldn't be after reload, but just in case)
        if (fileInput.files && fileInput.files.length > 0) {
            updateFileList(fileInput.files);
        }
    }
});
